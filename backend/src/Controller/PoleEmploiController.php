<?php

namespace App\Controller;

use App\Service\PoleEmploiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/pole-emploi', name: 'api_pole_emploi_')]
class PoleEmploiController extends AbstractController
{
    private PoleEmploiService $poleEmploiService;
    private LoggerInterface $logger;

    public function __construct(PoleEmploiService $poleEmploiService, LoggerInterface $logger)
    {
        $this->poleEmploiService = $poleEmploiService;
        $this->logger = $logger;
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
     * Rechercher des offres d'emploi
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $criteria = [
                'keywords' => $request->query->get('keywords', ''),
                'location' => $request->query->get('location', ''),
                'distance' => $request->query->getInt('distance', 10),
                'contractType' => $request->query->get('contractType', ''),
                'contract' => $request->query->get('contract', ''),
                'sector' => $request->query->get('sector', ''),
                'experience' => $request->query->get('experience', ''),
                'qualification' => $request->query->get('qualification', ''),
                'fullTime' => $request->query->getBoolean('fullTime', true),
                'sort' => $request->query->getInt('sort', 0),
                'limit' => $request->query->getInt('limit', 20),
                'page' => $request->query->getInt('page', 1)
            ];

            // Nettoyer les critères vides
            $criteria = array_filter($criteria, function($value) {
                return $value !== '' && $value !== null;
            });

            // Utiliser uniquement l'API événements France Travail
            $eventResults = $this->poleEmploiService->searchJobs($criteria);
            
            $this->logger->info('Événements France Travail récupérés', [
                'count' => $eventResults['total'] ?? 0,
                'criteria' => $criteria
            ]);

            return new JsonResponse([
                'success' => true,
                'data' => $eventResults,
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
                    'total' => 0
                ]
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'une offre
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
}
