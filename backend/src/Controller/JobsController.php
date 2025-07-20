<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/jobs', name: 'api_jobs_')]
class JobsController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Endpoint de test
     */
    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => 'API Jobs fonctionne !',
            'service' => 'Test Data Generator',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
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
                'limit' => $request->query->getInt('limit', 20)
            ];

            // Nettoyer les critères vides
            $criteria = array_filter($criteria, function($value) {
                return $value !== '' && $value !== null;
            });

            try {
                // Générer des données de test pour les offres d'emploi
                $this->logger->info('Génération de données de test pour les offres d\'emploi');
                
                $testResults = $this->generateTestJobs($criteria);

                return new JsonResponse([
                    'success' => true,
                    'data' => $testResults,
                    'message' => 'Recherche effectuée avec succès (données de test)',
                    'source' => 'test_data'
                ]);

            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la génération des données de test: ' . $e->getMessage());
                
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Erreur lors de la recherche',
                    'error' => $e->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la recherche d\'offres', [
                'error' => $e->getMessage(),
                'criteria' => $criteria ?? []
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la recherche',
                'error' => $e->getMessage()
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
            // Service de données de test - détails non disponibles pour cette démo
            $this->logger->info('Détails des offres non disponibles dans la version test, job ID: ' . $id);

            return new JsonResponse([
                'success' => false,
                'message' => 'Détails des offres non disponibles - version de démonstration',
                'error' => 'Fonctionnalité non implémentée'
            ], 404);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des détails', [
                'error' => $e->getMessage(),
                'job_id' => $id
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tester la disponibilité du service de données de test
     */
    #[Route('/test-auth', name: 'test_auth', methods: ['GET'])]
    public function testAuth(): JsonResponse
    {
        try {
            // Service de test actif
            $this->logger->info('Test du service de données de test - fonctionnel');

            return new JsonResponse([
                'success' => true,
                'message' => 'Service de test des offres d\'emploi fonctionnel',
                'results_count' => 2
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur test du service',
                'error' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * Générer des données de test (fallback)
     */
    private function generateTestJobs(array $criteria): array
    {
        $allJobs = [
            // Informatique / Tech
            [
                'id' => '1',
                'title' => 'Développeur Web Full Stack',
                'company' => 'TechCorp',
                'location' => 'Paris',
                'contract_type' => 'CDI',
                'experience' => 'Débutant accepté',
                'qualification' => 'Bac+3',
                'description' => 'Nous recherchons un développeur passionné pour rejoindre notre équipe et travailler sur des projets innovants.',
                'salary' => '35-45K€',
                'publication_date' => '2025-07-18',
                'application_url' => 'https://example.com/apply/1',
                'sector' => 'Informatique',
                'keywords' => 'développeur web full stack javascript react node'
            ],
            [
                'id' => '2',
                'title' => 'Développeur Frontend React',
                'company' => 'StartupXYZ',
                'location' => 'Lyon',
                'contract_type' => 'CDD',
                'experience' => 'Souhaité',
                'qualification' => 'Bac+5',
                'description' => 'Rejoignez notre startup en croissance pour développer des interfaces modernes avec React et TypeScript.',
                'salary' => '40-50K€',
                'publication_date' => '2025-07-17',
                'application_url' => 'https://example.com/apply/2',
                'sector' => 'Informatique',
                'keywords' => 'développeur frontend react typescript javascript'
            ],
            // Ajoutez les autres offres ici...
        ];

        // Filtrer selon les critères
        $filteredJobs = array_filter($allJobs, function($job) use ($criteria) {
            // Filtrer par mots-clés
            if (!empty($criteria['keywords'])) {
                $searchText = strtolower($job['title'] . ' ' . $job['description'] . ' ' . $job['keywords']);
                $searchKeywords = strtolower($criteria['keywords']);
                if (strpos($searchText, $searchKeywords) === false) {
                    return false;
                }
            }

            // Filtrer par localisation
            if (!empty($criteria['location'])) {
                $jobLocation = strtolower($job['location']);
                $searchLocation = strtolower($criteria['location']);
                if (strpos($jobLocation, $searchLocation) === false) {
                    return false;
                }
            }

            // Filtrer par secteur
            if (!empty($criteria['sector'])) {
                if (strcasecmp($job['sector'], $criteria['sector']) !== 0) {
                    return false;
                }
            }

            return true;
        });

        // Appliquer la limite
        $limit = $criteria['limit'] ?? 20;
        $filteredJobs = array_slice(array_values($filteredJobs), 0, $limit);

        return [
            'jobs' => $filteredJobs,
            'total' => count($filteredJobs)
        ];
    }
}
