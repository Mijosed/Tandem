<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Repository\CandidatureRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class CandidatureController extends AbstractController
{
    public function __construct(
        private CandidatureRepository $candidatureRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('/candidatures/check/{jobId}', name: 'candidature_check', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function checkCandidature(string $jobId): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        $candidature = $this->candidatureRepository->findByUserAndJobId($user, $jobId);

        return new JsonResponse([
            'hasApplied' => $candidature !== null,
            'candidature' => $candidature ? [
                'id' => $candidature->getId(),
                'status' => $candidature->getStatut(),
                'createdAt' => $candidature->getDateCreation()->format('Y-m-d H:i:s')
            ] : null
        ]);
    }

    #[Route('/candidatures/check-multiple', name: 'candidature_check_multiple', methods: ['POST'])]
    // #[IsGranted('ROLE_USER')] // Temporairement désactivé - utilise X-User-ID header
    public function checkMultipleCandidatures(Request $request): JsonResponse
    {
        // Récupérer l'utilisateur depuis le header X-User-ID (temporaire pour dev)
        $userId = $request->headers->get('X-User-ID');
        
        $user = null;
        if ($userId) {
            $user = $this->userRepository->find($userId);
        }
        
        $data = json_decode($request->getContent(), true);
        $jobIds = $data['jobIds'] ?? [];

        if (empty($jobIds)) {
            return new JsonResponse(['error' => 'No job IDs provided'], 400);
        }

        $results = [];
        foreach ($jobIds as $jobId) {
            if ($user) {
                $candidature = $this->candidatureRepository->findByUserAndJobId($user, $jobId);
                $results[$jobId] = [
                    'hasApplied' => $candidature !== null,
                    'candidature' => $candidature ? [
                        'id' => $candidature->getId(),
                        'status' => $candidature->getStatut(),
                        'createdAt' => $candidature->getDateCreation()->format('Y-m-d H:i:s')
                    ] : null
                ];
            } else {
                // Aucun utilisateur, retourner false pour toutes les candidatures
                $results[$jobId] = [
                    'hasApplied' => false,
                    'candidature' => null
                ];
            }
        }

        return new JsonResponse($results);
    }
}
