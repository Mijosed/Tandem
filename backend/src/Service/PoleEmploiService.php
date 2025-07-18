<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class PoleEmploiService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private ?string $accessToken = null;
    private ?\DateTime $tokenExpiry = null;

    // Identifiants Pôle Emploi (à configurer)
    private string $clientId;
    private string $clientSecret;
    private string $scope = 'api_offresdemploiv2 o2dsoffre';

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        string $poleEmploiClientId = '',
        string $poleEmploiClientSecret = ''
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->clientId = $poleEmploiClientId;
        $this->clientSecret = $poleEmploiClientSecret;
    }

    /**
     * Obtenir un token d'accès OAuth2
     */
    public function getAccessToken(): string
    {
        // Si le token est encore valide, le retourner
        if ($this->accessToken && $this->tokenExpiry && $this->tokenExpiry > new \DateTime()) {
            return $this->accessToken;
        }

        try {
            $this->logger->info('Tentative d\'obtention du token Pôle Emploi', [
                'client_id' => $this->clientId,
                'scope' => $this->scope
            ]);

            // Utiliser curl car HttpClient semble poser problème
            $data = http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => $this->scope
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $data
                ]
            ]);

            $result = @file_get_contents('https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=/partenaire', false, $context);
            
            if ($result === false) {
                $error = error_get_last();
                $this->logger->error('Erreur lors de l\'obtention du token Pôle Emploi', [
                    'error' => $error['message'] ?? 'Erreur inconnue'
                ]);
                throw new \Exception('Erreur de connexion à l\'API Pôle Emploi');
            }

            $responseData = json_decode($result, true);
            
            if (isset($responseData['error'])) {
                $this->logger->error('Erreur API Pôle Emploi', [
                    'error' => $responseData['error'],
                    'description' => $responseData['error_description'] ?? 'Pas de description'
                ]);
                throw new \Exception('Erreur d\'authentification Pôle Emploi: ' . $responseData['error_description']);
            }

            if (!isset($responseData['access_token'])) {
                $this->logger->error('Réponse inattendue de l\'API Pôle Emploi', ['response' => $responseData]);
                throw new \Exception('Token non trouvé dans la réponse');
            }

            $this->accessToken = $responseData['access_token'];
            $this->tokenExpiry = new \DateTime('+' . ($responseData['expires_in'] - 60) . ' seconds');

            $this->logger->info('Token Pôle Emploi obtenu avec succès');
            return $this->accessToken;

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'obtention du token Pôle Emploi: ' . $e->getMessage());
            throw new \Exception('Impossible d\'obtenir le token d\'accès Pôle Emploi: ' . $e->getMessage());
        }
    }

    /**
     * Rechercher des offres d'emploi
     */
    public function searchJobs(array $criteria = []): array
    {
        try {
            $token = $this->getAccessToken();
            
            // Construire les paramètres de recherche
            $params = [];
            
            if (!empty($criteria['keywords'])) {
                $params['motsCles'] = $criteria['keywords'];
            }
            if (!empty($criteria['location'])) {
                $params['commune'] = $criteria['location'];
            }
            if (!empty($criteria['sector'])) {
                $params['secteurActivite'] = $criteria['sector'];
            }
            if (!empty($criteria['contract_type'])) {
                $params['typeContrat'] = $criteria['contract_type'];
            }
            if (!empty($criteria['experience'])) {
                $params['experienceExigee'] = $criteria['experience'];
            }
            
            // Paramètres par défaut
            $limit = $criteria['limit'] ?? 20; // Par défaut 20 résultats pour pagination web
            $page = $criteria['page'] ?? 1; // Page 1 par défaut
            $offset = ($page - 1) * $limit;
            
            $params['range'] = $offset . '-' . ($offset + $limit - 1); // Pagination
            $params['sort'] = '1'; // Tri par date de création
            
            $url = 'https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search';
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
            
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ]
            ]);

            $data = $response->toArray();
            
            $this->logger->info('Recherche d\'offres France Travail effectuée', [
                'criteria' => $criteria,
                'params' => $params,
                'results_count' => isset($data['resultats']) ? count($data['resultats']) : 0
            ]);

            return $this->formatJobResults($data, $criteria);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la recherche d\'offres: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la recherche d\'offres d\'emploi: ' . $e->getMessage());
        }
    }

    /**
     * Rechercher des événements emploi
     */
    public function searchEvents(array $criteria = []): array
    {
        try {
            $token = $this->getAccessToken();
            
            $response = $this->httpClient->request('GET', 'https://api.francetravail.io/partenaire/evenements/v1/salonsenligne', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ]
            ]);

            $data = $response->toArray();
            
            $this->logger->info('Recherche d\'événements France Travail effectuée', [
                'criteria' => $criteria,
                'results_count' => count($data)
            ]);

            return $data;

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la recherche d\'événements: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la recherche d\'événements emploi');
        }
    }

    /**
     * Obtenir les détails d'une offre
     */
    public function getJobDetails(string $jobId): array
    {
        try {
            $token = $this->getAccessToken();

            $response = $this->httpClient->request('GET', "https://api.francetravail.io/partenaire/offresdemploi/v2/offres/{$jobId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ]
            ]);

            $data = $response->toArray();
            return $this->formatJobDetails($data);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des détails: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la récupération des détails de l\'offre');
        }
    }

    /**
     * Formater les résultats de recherche
     */
    private function formatJobResults(array $data, array $criteria = []): array
    {
        if (!isset($data['resultats'])) {
            return ['jobs' => [], 'total' => 0, 'pagination' => []];
        }

        $jobs = [];
        foreach ($data['resultats'] as $job) {
            $jobs[] = [
                'id' => $job['id'],
                'title' => $job['intitule'],
                'company' => $job['entreprise']['nom'] ?? 'Non spécifié',
                'location' => $job['lieuTravail']['libelle'] ?? '',
                'contract_type' => $job['typeContrat'] ?? '',
                'experience' => $job['experienceExige'] ?? '',
                'qualification' => $job['qualificationLibelle'] ?? '',
                'description' => strip_tags($job['description'] ?? ''),
                'salary' => $job['salaire']['libelle'] ?? null,
                'publication_date' => $job['dateCreation'] ?? '',
                'application_url' => $job['origineOffre']['urlOrigine'] ?? '',
                'duration' => $job['dureeTravailLibelle'] ?? null,
                'sector' => $job['secteurActivite'] ?? null
            ];
        }

        // Informations de pagination avec les vraies valeurs
        $currentPage = $criteria['page'] ?? 1;
        $perPage = $criteria['limit'] ?? 20;
        $actualResults = $data['resultats'] ? count($data['resultats']) : 0;
        
        // Calculer s'il y a potentiellement plus de pages
        // Si on a reçu exactement le nombre demandé, il y a probablement une page suivante
        $hasNextPage = $actualResults === $perPage;
        $hasPreviousPage = $currentPage > 1;
        
        // Estimation du total (on ne peut pas connaître le vrai total avec l'API France Travail)
        $estimatedTotal = $hasNextPage ? ($currentPage * $perPage) + 1 : ($currentPage - 1) * $perPage + $actualResults;
        
        $pagination = [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total_results' => $actualResults, // Résultats de cette page
            'estimated_total' => $estimatedTotal, // Estimation du total
            'has_next_page' => $hasNextPage,
            'has_previous_page' => $hasPreviousPage,
            'next_page' => $hasNextPage ? $currentPage + 1 : null,
            'previous_page' => $hasPreviousPage ? $currentPage - 1 : null,
            'total_pages' => $hasNextPage ? '?' : $currentPage // Inconnu si page suivante existe
        ];

        return [
            'jobs' => $jobs,
            'total' => $actualResults,
            'pagination' => $pagination,
            'filters' => $data['filtresPossibles'] ?? []
        ];
    }

    /**
     * Formater les résultats d'événements comme des offres
     */
    private function formatEventResults(array $events): array
    {
        $jobs = [];
        foreach ($events as $event) {
            $jobs[] = [
                'id' => 'event_' . ($event['id'] ?? uniqid()),
                'title' => $event['titre'] ?? 'Événement emploi',
                'company' => $event['organismeOrganisateur'] ?? 'France Travail',
                'location' => $event['localisation'] ?? 'Non spécifié',
                'contract_type' => 'Événement emploi',
                'experience' => 'Tous niveaux',
                'qualification' => 'Tous niveaux',
                'description' => strip_tags($event['description'] ?? 'Salon ou forum emploi avec ' . ($event['nombreOffres'] ?? 0) . ' offres disponibles'),
                'salary' => null,
                'publication_date' => $this->convertDate($event['dateDebut'] ?? ''),
                'application_url' => $event['urlSalonEnLigne'] ?? '',
                'duration' => $this->formatEventDuration($event),
                'sector' => 'Événement emploi',
                'event_type' => true,
                'offers_count' => $event['nombreOffres'] ?? 0,
                'event_start' => $event['dateDebut'] ?? '',
                'event_end' => $event['dateFin'] ?? '',
                'is_active' => $event['salonEnCours'] ?? false
            ];
        }

        return [
            'jobs' => $jobs,
            'total' => count($jobs),
            'filters' => []
        ];
    }

    /**
     * Convertir le format de date français
     */
    private function convertDate(string $frenchDate): string
    {
        if (empty($frenchDate)) {
            return '';
        }
        
        try {
            $date = \DateTime::createFromFormat('d/m/Y', $frenchDate);
            return $date ? $date->format('Y-m-d') : $frenchDate;
        } catch (\Exception $e) {
            return $frenchDate;
        }
    }

    /**
     * Formater la durée de l'événement
     */
    private function formatEventDuration(array $event): ?string
    {
        $start = $event['dateDebut'] ?? '';
        $end = $event['dateFin'] ?? '';
        
        if ($start && $end) {
            return "Du {$start} au {$end}";
        }
        
        return null;
    }

    /**
     * Formater les détails d'une offre
     */
    private function formatJobDetails(array $data): array
    {
        return [
            'id' => $data['id'],
            'title' => $data['intitule'],
            'company' => [
                'name' => $data['entreprise']['nom'] ?? 'Non spécifié',
                'description' => $data['entreprise']['description'] ?? null
            ],
            'location' => $data['lieuTravail']['libelle'] ?? '',
            'contract_type' => $data['typeContrat'] ?? '',
            'experience' => $data['experienceExige'] ?? '',
            'qualification' => $data['qualificationLibelle'] ?? '',
            'description' => $data['description'] ?? '',
            'profile' => $data['qualitesProfessionnelles'] ?? '',
            'salary' => $data['salaire']['libelle'] ?? null,
            'publication_date' => $data['dateCreation'] ?? '',
            'application_url' => $data['origineOffre']['urlOrigine'] ?? '',
            'duration' => $data['dureeTravailLibelle'] ?? null,
            'sector' => $data['secteurActivite'] ?? null,
            'formation' => $data['formationExigee'] ?? null,
            'languages' => $data['langues'] ?? [],
            'skills' => $data['competences'] ?? []
        ];
    }

    /**
     * Obtenir les secteurs d'activité
     */
    public function getSectors(): array
    {
        try {
            $token = $this->getAccessToken();

            $response = $this->httpClient->request('GET', 'https://api.francetravail.io/partenaire/offresdemploi/v2/referentiel/secteursActivites', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ]
            ]);

            return $response->toArray();

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des secteurs: ' . $e->getMessage());
            return [];
        }
    }
}
