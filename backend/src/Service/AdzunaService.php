<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class AdzunaService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    // Identifiants Adzuna (gratuits et faciles à obtenir)
    private string $appId;
    private string $appKey;
    private string $baseUrl = 'https://api.adzuna.com/v1/api/jobs/fr/search';

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        string $adzunaAppId = '',
        string $adzunaAppKey = ''
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->appId = $adzunaAppId;
        $this->appKey = $adzunaAppKey;
    }

    /**
     * Rechercher des offres d'emploi
     */
    public function searchJobs(array $criteria = []): array
    {
        try {
            // Paramètres de recherche
            $params = [
                'app_id' => $this->appId,
                'app_key' => $this->appKey,
                'results_per_page' => $criteria['limit'] ?? 20,
                'what' => $criteria['keywords'] ?? '',
                'where' => $criteria['location'] ?? '',
                'distance' => $criteria['distance'] ?? 10,
                'sort_by' => 'relevance', // ou 'date'
                'page' => 1
            ];

            // Nettoyer les paramètres vides
            $params = array_filter($params, function($value) {
                return $value !== '' && $value !== null;
            });

            $response = $this->httpClient->request('GET', $this->baseUrl . '/1', [
                'query' => $params,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'Tandem-App/1.0'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new \Exception("Erreur HTTP $statusCode");
            }

            $data = $response->toArray();
            
            $this->logger->info('Recherche Adzuna effectuée', [
                'criteria' => $criteria,
                'results_count' => $data['count'] ?? 0
            ]);

            return $this->formatJobResults($data);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la recherche Adzuna: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la recherche d\'offres d\'emploi: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les détails d'une offre
     */
    public function getJobDetails(string $jobId): array
    {
        try {
            // Adzuna ne fournit pas d'endpoint détails, on renvoie les infos de base
            return [
                'id' => $jobId,
                'message' => 'Détails complets disponibles via le lien de candidature'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des détails Adzuna: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la récupération des détails de l\'offre');
        }
    }

    /**
     * Formater les résultats de recherche
     */
    private function formatJobResults(array $data): array
    {
        if (!isset($data['results'])) {
            return ['jobs' => [], 'total' => 0];
        }

        $jobs = [];
        foreach ($data['results'] as $job) {
            $jobs[] = [
                'id' => $job['id'] ?? uniqid(),
                'title' => $job['title'] ?? 'Sans titre',
                'company' => $job['company']['display_name'] ?? 'Entreprise non spécifiée',
                'location' => $this->formatLocation($job['location'] ?? []),
                'contract_type' => $job['contract_type'] ?? 'Non spécifié',
                'experience' => 'Voir description',
                'qualification' => 'Voir description',
                'description' => strip_tags($job['description'] ?? ''),
                'salary' => $this->formatSalary($job),
                'publication_date' => $this->formatDate($job['created'] ?? ''),
                'application_url' => $job['redirect_url'] ?? '',
                'duration' => 'Non spécifié',
                'sector' => $job['category']['label'] ?? 'Non spécifié',
                'keywords' => implode(', ', array_slice(explode(' ', $job['title'] ?? ''), 0, 3))
            ];
        }

        return [
            'jobs' => $jobs,
            'total' => $data['count'] ?? count($jobs),
            'filters' => []
        ];
    }

    /**
     * Formater la localisation
     */
    private function formatLocation(array $location): string
    {
        if (empty($location)) {
            return 'France';
        }

        $parts = [];
        if (isset($location['display_name'])) {
            return $location['display_name'];
        }
        
        if (isset($location['area'][3])) {
            $parts[] = $location['area'][3];
        }
        if (isset($location['area'][1])) {
            $parts[] = $location['area'][1];
        }

        return !empty($parts) ? implode(', ', $parts) : 'France';
    }

    /**
     * Formater le salaire
     */
    private function formatSalary(array $job): ?string
    {
        if (isset($job['salary_min']) && isset($job['salary_max'])) {
            $min = number_format($job['salary_min'], 0, ',', ' ');
            $max = number_format($job['salary_max'], 0, ',', ' ');
            return "{$min} - {$max}€";
        }
        
        if (isset($job['salary_min'])) {
            $min = number_format($job['salary_min'], 0, ',', ' ');
            return "À partir de {$min}€";
        }

        return 'Salaire non communiqué';
    }

    /**
     * Formater la date
     */
    private function formatDate(string $date): string
    {
        if (empty($date)) {
            return date('Y-m-d');
        }

        try {
            return (new \DateTime($date))->format('Y-m-d');
        } catch (\Exception $e) {
            return date('Y-m-d');
        }
    }

    /**
     * Obtenir les secteurs d'activité (simulé)
     */
    public function getSectors(): array
    {
        return [
            ['code' => 'it-jobs', 'label' => 'Informatique'],
            ['code' => 'marketing-pr-jobs', 'label' => 'Marketing'],
            ['code' => 'sales-jobs', 'label' => 'Commerce'],
            ['code' => 'admin-jobs', 'label' => 'Administration'],
            ['code' => 'healthcare-nursing-jobs', 'label' => 'Santé'],
            ['code' => 'engineering-jobs', 'label' => 'Ingénierie'],
            ['code' => 'education-jobs', 'label' => 'Éducation'],
            ['code' => 'creative-design-jobs', 'label' => 'Design'],
            ['code' => 'accounting-finance-jobs', 'label' => 'Finance']
        ];
    }
}
