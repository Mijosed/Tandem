<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use App\Repository\UserRepository;
use App\Repository\SubscriptionRepository;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private UserRepository $userRepository,
        private SubscriptionRepository $subscriptionRepository,
        private JwtService $jwtService
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['password'])) {
            return $this->json(['error' => 'Email et mot de passe requis'], 400);
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Un utilisateur avec cet email existe déjà'], 409);
        }

        // Créer l'utilisateur
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName'] ?? null);
        $user->setLastName($data['lastName'] ?? null);
        
        // Hacher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Valider l'utilisateur
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        // Créer l'abonnement gratuit par défaut
        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setPlan('free');
        $subscription->setStatus('active');

        $this->entityManager->persist($user);
        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'roles' => $user->getRoles(),
                'subscription' => [
                    'plan' => $subscription->getPlan(),
                    'status' => $subscription->getStatus()
                ]
            ]
        ], 201);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $rawContent = $request->getContent();
        $data = json_decode($rawContent, true);

        if (!$data || !isset($data['email'], $data['password'])) {
            return $this->json(['error' => 'Email et mot de passe requis'], 400);
        }

        $user = $this->userRepository->findOneBy(['email' => $data['email']]);
        if (!$user) {
            return $this->json(['error' => 'Identifiants invalides'], 401);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Identifiants invalides'], 401);
        }

        if (!$user->isActive()) {
            return $this->json(['error' => 'Compte désactivé'], 401);
        }

        try {
            $subscription = $this->subscriptionRepository->findByUser($user->getId());
        } catch (\Exception $e) {
            // En cas d'erreur avec la subscription, on continue sans
            $subscription = null;
        }

        // Utilisation du service JwtService pour générer le JWT
        $jwt = $this->jwtService->generate([
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'exp' => time() + 3600
        ]);

        return $this->json([
            'message' => 'Connexion réussie',
            'token' => $jwt,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'fullName' => $user->getFullName(),
                'roles' => $user->getRoles(),
                'subscription' => $subscription ? [
                    'plan' => $subscription->getPlan(),
                    'status' => $subscription->getStatus(),
                    'isPremium' => $subscription->getPlan() === 'premium'
                ] : [
                    'plan' => 'free',
                    'status' => 'active',
                    'isPremium' => false
                ]
            ]
        ]);
    }

    #[Route('/forgot-password', name: 'forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'])) {
            return $this->json(['error' => 'Email requis'], 400);
        }

        $user = $this->userRepository->findOneBy(['email' => $data['email']]);
        if (!$user) {
            // Pour des raisons de sécurité, ne pas révéler si l'email existe
            return $this->json(['message' => 'Si cet email existe, un lien de réinitialisation a été envoyé'], 200);
        }

        // TODO: Implémenter la logique d'envoi d'email de réinitialisation
        // - Générer un token sécurisé
        // - Sauvegarder le token avec une date d'expiration
        // - Envoyer l'email avec le lien de réinitialisation

        return $this->json(['message' => 'Si cet email existe, un lien de réinitialisation a été envoyé'], 200);
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['token'], $data['password'])) {
            return $this->json(['error' => 'Token et nouveau mot de passe requis'], 400);
        }

        // TODO: Implémenter la logique de réinitialisation
        // - Vérifier la validité du token
        // - Vérifier que le token n'a pas expiré
        // - Mettre à jour le mot de passe
        // - Invalider le token

        return $this->json(['message' => 'Mot de passe réinitialisé avec succès']);
    }
}
