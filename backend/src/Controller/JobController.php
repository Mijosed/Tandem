<?php

namespace App\Controller;

use App\Entity\Job;
use App\Repository\JobRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/jobs', name: 'api_jobs_')]
class JobController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private JobRepository $jobRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function listJobs(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $type = $request->query->get('type', '');
        $location = $request->query->get('location', '');
        $company = $request->query->get('company', '');
        $premiumOnly = $request->query->getBoolean('premium', false);
        $limit = $request->query->getInt('limit', 20);

        $filters = [
            'query' => $query,
            'type' => $type,
            'location' => $location,
            'company' => $company,
            'premiumOnly' => $premiumOnly
        ];

        $jobs = $this->jobRepository->findByFilters($filters);
        $jobs = array_slice($jobs, 0, $limit);

        return $this->json([
            'jobs' => array_map(function($job) {
                return [
                    'id' => $job->getId(),
                    'title' => $job->getTitle(),
                    'company' => $job->getCompany(),
                    'location' => $job->getLocation(),
                    'type' => $job->getType(),
                    'salary' => $job->getSalary(),
                    'description' => $job->getDescription(),
                    'postedDate' => $job->getPostedDate()->format('Y-m-d'),
                    'sourceUrl' => $job->getSourceUrl(),
                    'source' => $job->getSource(),
                    'isPremium' => $job->isPremium(),
                    'createdAt' => $job->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }, $jobs),
            'total' => count($jobs)
        ]);
    }

    #[Route('/search', name: 'search', methods: ['POST'])]
    public function searchJobs(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $query = $data['query'] ?? '';
        $userId = $data['userId'] ?? null;
        $premiumOnly = $data['premiumOnly'] ?? false;

        // Vérifier si l'utilisateur est premium pour les recherches premium
        if ($premiumOnly && $userId) {
            $user = $this->userRepository->find($userId);
            if (!$user || !$user->isPremium()) {
                return $this->json(['error' => 'Accès premium requis'], 403);
            }
        }

        $jobs = $this->jobRepository->searchJobs($query, $premiumOnly);

        return $this->json([
            'jobs' => array_map(function($job) {
                return [
                    'id' => $job->getId(),
                    'title' => $job->getTitle(),
                    'company' => $job->getCompany(),
                    'location' => $job->getLocation(),
                    'type' => $job->getType(),
                    'salary' => $job->getSalary(),
                    'description' => $job->getDescription(),
                    'postedDate' => $job->getPostedDate()->format('Y-m-d'),
                    'sourceUrl' => $job->getSourceUrl(),
                    'source' => $job->getSource(),
                    'isPremium' => $job->isPremium()
                ];
            }, $jobs),
            'total' => count($jobs),
            'isPremiumSearch' => $premiumOnly
        ]);
    }

    #[Route('/recent', name: 'recent', methods: ['GET'])]
    public function getRecentJobs(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $premiumOnly = $request->query->getBoolean('premium', false);

        $jobs = $this->jobRepository->findRecentJobs($limit, $premiumOnly);

        return $this->json([
            'jobs' => array_map(function($job) {
                return [
                    'id' => $job->getId(),
                    'title' => $job->getTitle(),
                    'company' => $job->getCompany(),
                    'location' => $job->getLocation(),
                    'type' => $job->getType(),
                    'salary' => $job->getSalary(),
                    'description' => substr($job->getDescription(), 0, 200) . '...',
                    'postedDate' => $job->getPostedDate()->format('Y-m-d'),
                    'sourceUrl' => $job->getSourceUrl(),
                    'isPremium' => $job->isPremium()
                ];
            }, $jobs)
        ]);
    }

    #[Route('/types', name: 'types', methods: ['GET'])]
    public function getJobTypes(): JsonResponse
    {
        return $this->json([
            'types' => [
                'Alternance',
                'Stage',
                'CDI',
                'CDD',
                'Freelance'
            ]
        ]);
    }

    #[Route('/locations', name: 'locations', methods: ['GET'])]
    public function getPopularLocations(): JsonResponse
    {
        // Récupérer les locations les plus populaires
        $qb = $this->entityManager->createQueryBuilder();
        $locations = $qb
            ->select('j.location, COUNT(j.id) as count')
            ->from(Job::class, 'j')
            ->where('j.isActive = true')
            ->andWhere('j.location IS NOT NULL')
            ->groupBy('j.location')
            ->orderBy('count', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->json([
            'locations' => array_map(function($location) {
                return [
                    'name' => $location['location'],
                    'count' => (int) $location['count']
                ];
            }, $locations)
        ]);
    }

    #[Route('/companies', name: 'companies', methods: ['GET'])]
    public function getPopularCompanies(): JsonResponse
    {
        // Récupérer les entreprises les plus populaires
        $qb = $this->entityManager->createQueryBuilder();
        $companies = $qb
            ->select('j.company, COUNT(j.id) as count')
            ->from(Job::class, 'j')
            ->where('j.isActive = true')
            ->groupBy('j.company')
            ->orderBy('count', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->json([
            'companies' => array_map(function($company) {
                return [
                    'name' => $company['company'],
                    'count' => (int) $company['count']
                ];
            }, $companies)
        ]);
    }

    #[Route('/scrape-indeed', name: 'scrape_indeed', methods: ['POST'])]
    public function scrapeIndeedJobs(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $query = $data['query'] ?? 'alternance';
        $location = $data['location'] ?? 'France';

        // Simulation du scraping Indeed
        // En production, vous utiliseriez une API ou un scraper réel
        $scrapedJobs = [
            [
                'title' => 'Développeur Full Stack en Alternance',
                'company' => 'Tech Solutions',
                'location' => 'Paris (75)',
                'type' => 'Alternance',
                'salary' => '1250€/mois',
                'description' => 'Rejoignez notre équipe en tant que développeur full stack. Technologies : React, Node.js, PostgreSQL.',
                'sourceUrl' => 'https://indeed.fr/viewjob?jk=example1',
                'source' => 'indeed'
            ],
            [
                'title' => 'Alternance Data Scientist',
                'company' => 'Data Corp',
                'location' => 'Lyon (69)',
                'type' => 'Alternance',
                'salary' => '1400€/mois',
                'description' => 'Opportunité unique en Data Science. Python, ML, Big Data.',
                'sourceUrl' => 'https://indeed.fr/viewjob?jk=example2',
                'source' => 'indeed'
            ]
        ];

        // Sauvegarder les jobs scrapés
        $savedJobs = [];
        foreach ($scrapedJobs as $jobData) {
            $job = new Job();
            $job->setTitle($jobData['title']);
            $job->setCompany($jobData['company']);
            $job->setLocation($jobData['location']);
            $job->setType($jobData['type']);
            $job->setSalary($jobData['salary']);
            $job->setDescription($jobData['description']);
            $job->setSourceUrl($jobData['sourceUrl']);
            $job->setSource($jobData['source']);
            $job->setIsPremium(true); // Les jobs Indeed sont premium
            $job->setPostedDate(new \DateTime());

            $this->entityManager->persist($job);
            $savedJobs[] = $job;
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Jobs scrapés avec succès',
            'count' => count($savedJobs),
            'jobs' => array_map(function($job) {
                return [
                    'id' => $job->getId(),
                    'title' => $job->getTitle(),
                    'company' => $job->getCompany(),
                    'location' => $job->getLocation(),
                    'type' => $job->getType(),
                    'salary' => $job->getSalary(),
                    'description' => substr($job->getDescription(), 0, 200) . '...',
                    'sourceUrl' => $job->getSourceUrl(),
                    'source' => $job->getSource()
                ];
            }, $savedJobs)
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getJob(int $id): JsonResponse
    {
        $job = $this->jobRepository->find($id);
        if (!$job) {
            return $this->json(['error' => 'Offre non trouvée'], 404);
        }

        return $this->json([
            'job' => [
                'id' => $job->getId(),
                'title' => $job->getTitle(),
                'company' => $job->getCompany(),
                'location' => $job->getLocation(),
                'type' => $job->getType(),
                'salary' => $job->getSalary(),
                'description' => $job->getDescription(),
                'postedDate' => $job->getPostedDate()->format('Y-m-d'),
                'sourceUrl' => $job->getSourceUrl(),
                'source' => $job->getSource(),
                'isPremium' => $job->isPremium(),
                'isActive' => $job->isActive(),
                'createdAt' => $job->getCreatedAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }
}
