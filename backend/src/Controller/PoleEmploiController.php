<?php

namespace App\Controller;

use App\Service\PoleEmploiService;
use App\Service\GeolocationService;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/pole-emploi', name: 'api_pole_emploi_')]
class PoleEmploiController extends AbstractController
{
    private PoleEmploiService $poleEmploiService;
    private GeolocationService $geolocationService;
    private LoggerInterface $logger;
    private UserRepository $userRepository;

    public function __construct(
        PoleEmploiService $poleEmploiService, 
        GeolocationService $geolocationService,
        LoggerInterface $logger,
        UserRepository $userRepository
    ) {
        $this->poleEmploiService = $poleEmploiService;
        $this->geolocationService = $geolocationService;
        $this->logger = $logger;
        $this->userRepository = $userRepository;
    }

    /**
     * Test de l'API Pôle Emploi
     */
    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        try {
            $token = $this->poleEmploiService->getAccessToken();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Connexion à l\'API Pôle Emploi réussie',
                'token_preview' => substr($token, 0, 20) . '...'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur de connexion à l\'API Pôle Emploi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechercher des offres d'emploi (nécessite un abonnement premium)
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        // Vérifier l'abonnement premium
        $userId = $request->headers->get('X-User-ID');
        
        if (!$userId) {
            return new JsonResponse([
                'success' => false,
                'error' => 'premium_required',
                'message' => 'Un abonnement premium est requis pour accéder aux offres d\'emploi'
            ], 403);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'user_not_found',
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        if (!$user->isPremium()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'premium_required',
                'message' => 'Un abonnement premium est requis pour accéder aux offres d\'emploi',
                'subscription_status' => [
                    'plan' => $user->getSubscription()?->getPlan() ?? 'free',
                    'status' => $user->getSubscription()?->getStatus() ?? 'inactive'
                ]
            ], 403);
        }

        try {
            $criteria = [
                'keywords' => $request->query->get('keywords', ''),
                'location' => $request->query->get('location', ''),
                'sector' => $request->query->get('sector', ''),
                'contract_type' => $request->query->get('contract_type', ''),
                'experience' => $request->query->get('experience', ''),
                'limit' => $request->query->getInt('limit', 20),
                'page' => $request->query->getInt('page', 1)
            ];

            // Nettoyer les critères vides
            $criteria = array_filter($criteria, function($value) {
                return $value !== '' && $value !== null;
            });

            // Recherche via l'API France Travail
            $results = $this->poleEmploiService->searchJobs($criteria);

            return new JsonResponse([
                'success' => true,
                'data' => $results,
                'message' => 'Recherche effectuée avec succès'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la recherche d\'offres', [
                'error' => $e->getMessage(),
                'criteria' => $criteria ?? []
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la recherche d\'offres: ' . $e->getMessage(),
                'data' => [
                    'jobs' => [],
                    'total' => 0,
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => 20,
                        'total_results' => 0,
                        'has_next_page' => false,
                        'has_previous_page' => false
                    ]
                ]
            ], 500);
        }
    }

    /**
     * Obtenir les secteurs d'activité disponibles
     */
    #[Route('/sectors', name: 'sectors', methods: ['GET'])]
    public function getSectors(): JsonResponse
    {
        try {
            $sectors = $this->poleEmploiService->getSectors();

            return new JsonResponse([
                'success' => true,
                'data' => $sectors,
                'message' => 'Secteurs récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des secteurs', [
                'error' => $e->getMessage()
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération des secteurs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir des suggestions de recherche
     */
    #[Route('/suggestions', name: 'suggestions', methods: ['GET'])]
    public function getSuggestions(Request $request): JsonResponse
    {
        try {
            $query = $request->query->get('q', '');
            
            if (strlen($query) < 2) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'La requête doit contenir au moins 2 caractères'
                ], 400);
            }

            // Suggestions simples basées sur les secteurs populaires
            $suggestions = [
                'développeur', 'chef de projet', 'commercial', 'comptable', 
                'infirmier', 'professeur', 'ingénieur', 'technicien',
                'secrétaire', 'vendeur', 'chauffeur', 'cuisinier'
            ];

            $filteredSuggestions = array_filter($suggestions, function($suggestion) use ($query) {
                return stripos($suggestion, $query) !== false;
            });

            return new JsonResponse([
                'success' => true,
                'data' => array_values($filteredSuggestions),
                'message' => 'Suggestions générées avec succès'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la génération de suggestions', [
                'error' => $e->getMessage(),
                'query' => $request->query->get('q', '')
            ]);
            
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la génération de suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir des suggestions de localisation
     */
    #[Route('/locations', name: 'locations', methods: ['GET'])]
    public function getLocationSuggestions(Request $request): JsonResponse
    {
        try {
            $query = $request->query->get('q', '');
            
            if (strlen($query) < 2) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'La requête doit contenir au moins 2 caractères'
                ], 400);
            }

            $suggestions = $this->geolocationService->getLocationSuggestions($query);

            return new JsonResponse([
                'success' => true,
                'data' => $suggestions,
                'message' => 'Suggestions de localisation générées avec succès'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la génération de suggestions de localisation', [
                'error' => $e->getMessage(),
                'query' => $request->query->get('q', '')
            ]);
            
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la génération de suggestions de localisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'une offre (doit être en dernier car la route capture tout)
     */
    #[Route('/{id}', name: 'details', methods: ['GET'])]
    public function getJobDetails(string $id): JsonResponse
    {
        try {
            $jobDetails = $this->poleEmploiService->getJobDetails($id);

            return new JsonResponse([
                'success' => true,
                'data' => $jobDetails,
                'message' => 'Détails récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des détails', [
                'error' => $e->getMessage(),
                'job_id' => $id
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails: ' . $e->getMessage()
            ], 500);
        }
    }
}
