<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TotpService;
use App\Service\QrCodeService;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/auth/2fa', name: 'api_2fa_')]
class TwoFactorController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private TotpService $totpService,
        private QrCodeService $qrCodeService,
        private JwtService $jwtService,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    /**
     * Génère une clé secrète et un QR code pour activer le 2FA
     */
    #[Route('/setup', name: 'setup', methods: ['POST'])]
    public function setup(Request $request): JsonResponse
    {
        // Récupérer l'utilisateur depuis le header X-User-ID (temporaire)
        $userId = $request->headers->get('X-User-ID');
        
        if (!$userId) {
            return new JsonResponse(['error' => 'User ID required'], 400);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Vérifier le mot de passe pour des raisons de sécurité
        $data = json_decode($request->getContent(), true);
        if (!isset($data['password'])) {
            return new JsonResponse(['error' => 'Password required for 2FA setup'], 400);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid password'], 401);
        }

        try {
            // Générer une nouvelle clé secrète si elle n'existe pas encore
            $secret = $user->getTwoFactorSecret() ?: $this->totpService->generateSecret();
            $user->setTwoFactorSecret($secret);

            // Générer l'URL TOTP pour Google Authenticator
            $issuer = 'Tandem App';
            $accountName = $user->getEmail();
            $otpUrl = $this->totpService->getQrCodeUrl($secret, $issuer, $accountName);

            // Générer le QR code
            $qrCodeUrl = $this->qrCodeService->getQrCodeUrl($otpUrl, 200);

            // Générer des codes de récupération
            $backupCodes = $user->generateBackupCodes();

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'secret' => $secret,
                'qrCodeUrl' => $qrCodeUrl,
                'otpUrl' => $otpUrl,
                'backupCodes' => $backupCodes,
                'message' => '2FA setup initiated. Please verify with your authenticator app.'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to setup 2FA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Active le 2FA après vérification du code
     */
    #[Route('/enable', name: 'enable', methods: ['POST'])]
    public function enable(Request $request): JsonResponse
    {
        $userId = $request->headers->get('X-User-ID');
        
        if (!$userId) {
            return new JsonResponse(['error' => 'User ID required'], 400);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['code'])) {
            return new JsonResponse(['error' => 'TOTP code required'], 400);
        }

        $secret = $user->getTwoFactorSecret();
        if (!$secret) {
            return new JsonResponse(['error' => '2FA setup not initiated'], 400);
        }

        try {
            // Vérifier le code TOTP
            if (!$this->totpService->verifyCode($secret, $data['code'])) {
                return new JsonResponse(['error' => 'Invalid TOTP code'], 400);
            }

            // Activer le 2FA
            $user->setTwoFactorEnabled(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => '2FA has been successfully enabled',
                'backupCodes' => $user->getBackupCodes()
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to enable 2FA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Désactive le 2FA
     */
    #[Route('/disable', name: 'disable', methods: ['POST'])]
    public function disable(Request $request): JsonResponse
    {
        $userId = $request->headers->get('X-User-ID');
        
        if (!$userId) {
            return new JsonResponse(['error' => 'User ID required'], 400);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['password'])) {
            return new JsonResponse(['error' => 'Password required'], 400);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid password'], 401);
        }

        try {
            $user->setTwoFactorEnabled(false);
            $user->setTwoFactorSecret(null);
            $user->setBackupCodes(null);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => '2FA has been disabled'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to disable 2FA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifie un code TOTP pour finaliser la connexion
     */
    #[Route('/verify', name: 'verify', methods: ['POST'])]
    public function verify(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['email'], $data['code'])) {
            return new JsonResponse(['error' => 'Email and TOTP code required'], 400);
        }

        $user = $this->userRepository->findOneBy(['email' => $data['email']]);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        if (!$user->isTwoFactorEnabled()) {
            return new JsonResponse(['error' => '2FA not enabled for this user'], 400);
        }

        try {
            $isValidCode = false;
            $isBackupCode = false;

            // Vérifier le code TOTP
            if ($this->totpService->verifyCode($user->getTwoFactorSecret(), $data['code'])) {
                $isValidCode = true;
            }
            // Vérifier si c'est un code de récupération
            elseif ($user->useBackupCode($data['code'])) {
                $isValidCode = true;
                $isBackupCode = true;
                
                // Sauvegarder les codes mis à jour (avec le code utilisé retiré)
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }

            if (!$isValidCode) {
                return new JsonResponse(['error' => 'Invalid code'], 400);
            }

            // Générer le JWT complet
            $jwt = $this->jwtService->generate([
                'sub' => $user->getId(),
                'email' => $user->getEmail(),
                'exp' => time() + 3600
            ]);

            $response = [
                'success' => true,
                'message' => '2FA verification successful',
                'token' => $jwt,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'fullName' => $user->getFullName(),
                    'roles' => $user->getRoles(),
                    'twoFactorEnabled' => $user->isTwoFactorEnabled()
                ]
            ];

            if ($isBackupCode) {
                $response['warning'] = 'You used a backup code. Consider generating new backup codes.';
                $response['remainingBackupCodes'] = count($user->getBackupCodes() ?? []);
            }

            return new JsonResponse($response);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtient le statut du 2FA pour un utilisateur
     */
    #[Route('/status', name: 'status', methods: ['GET'])]
    public function status(Request $request): JsonResponse
    {
        $userId = $request->headers->get('X-User-ID');
        
        if (!$userId) {
            return new JsonResponse(['error' => 'User ID required'], 400);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        return new JsonResponse([
            'enabled' => $user->isTwoFactorEnabled(),
            'hasSecret' => $user->getTwoFactorSecret() !== null,
            'backupCodesCount' => count($user->getBackupCodes() ?? [])
        ]);
    }

    /**
     * Génère de nouveaux codes de récupération
     */
    #[Route('/backup-codes', name: 'backup_codes', methods: ['POST'])]
    public function generateBackupCodes(Request $request): JsonResponse
    {
        $userId = $request->headers->get('X-User-ID');
        
        if (!$userId) {
            return new JsonResponse(['error' => 'User ID required'], 400);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        if (!$user->isTwoFactorEnabled()) {
            return new JsonResponse(['error' => '2FA not enabled'], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['password'])) {
            return new JsonResponse(['error' => 'Password required'], 400);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid password'], 401);
        }

        try {
            $backupCodes = $user->generateBackupCodes();
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'backupCodes' => $backupCodes,
                'message' => 'New backup codes generated'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to generate backup codes: ' . $e->getMessage()
            ], 500);
        }
    }
} 