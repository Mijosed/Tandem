<?php

namespace App\Service;

use App\Repository\ApplicationRepository;
use App\Repository\NotificationRepository;
use App\Repository\ScheduleEventRepository;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;

class DashboardService
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private NotificationRepository $notificationRepository,
        private ScheduleEventRepository $scheduleEventRepository,
        private PaymentRepository $paymentRepository,
        private UserRepository $userRepository
    ) {}

    public function getUserStats(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $applicationStats = $this->applicationRepository->getApplicationsStats($userId);
        $upcomingInterviews = $this->applicationRepository->findUpcomingInterviews($userId);
        $unreadNotifications = $this->notificationRepository->countUnread($userId);
        $todayEvents = $this->scheduleEventRepository->findTodayEvents($userId);
        $upcomingEvents = $this->scheduleEventRepository->findUpcomingEvents($userId, 5);

        // Calcul du taux de réponse
        $responseRate = 0;
        if ($applicationStats['total'] > 0) {
            $responses = $applicationStats['interview'] + $applicationStats['accepted'] + $applicationStats['rejected'];
            $responseRate = round(($responses / $applicationStats['total']) * 100, 1);
        }

        return [
            'user' => [
                'id' => $user->getId(),
                'fullName' => $user->getFullName(),
                'email' => $user->getEmail(),
                'isPremium' => $user->isPremium()
            ],
            'stats' => [
                'applications' => $applicationStats['total'],
                'upcomingInterviews' => count($upcomingInterviews),
                'unreadNotifications' => $unreadNotifications,
                'responseRate' => $responseRate,
                'pendingResponses' => $applicationStats['pending'] + $applicationStats['followed_up'],
                'todayEvents' => count($todayEvents)
            ],
            'applicationsByStatus' => $applicationStats,
            'upcomingEvents' => array_map(function($event) {
                return [
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'startDate' => $event->getStartDate()->format('Y-m-d H:i:s'),
                    'endDate' => $event->getEndDate()->format('Y-m-d H:i:s'),
                    'type' => $event->getType(),
                    'location' => $event->getLocation()
                ];
            }, $upcomingEvents),
            'recentInterviews' => array_map(function($application) {
                return [
                    'id' => $application->getId(),
                    'position' => $application->getPosition(),
                    'company' => $application->getCompany(),
                    'interviewDate' => $application->getInterviewDate()?->format('Y-m-d H:i:s'),
                    'status' => $application->getStatus()
                ];
            }, array_slice($upcomingInterviews, 0, 3))
        ];
    }

    public function getApplicationTrends(int $userId, int $days = 30): array
    {
        $startDate = new \DateTime('-' . $days . ' days');
        $endDate = new \DateTime();
        
        // Simuler des données de tendance - en production, vous feriez une vraie requête
        $trends = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = new \DateTime('-' . $i . ' days');
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'applications' => rand(0, 3),
                'interviews' => rand(0, 1),
                'responses' => rand(0, 2)
            ];
        }

        return $trends;
    }

    public function getMonthlyProgress(int $userId): array
    {
        $currentMonth = new \DateTime('first day of this month');
        $lastMonth = new \DateTime('first day of last month');
        $lastMonthEnd = new \DateTime('last day of last month');

        // Statistiques du mois actuel
        $currentStats = $this->applicationRepository->getApplicationsStats($userId);
        
        // Ici, vous pourriez implémenter une logique pour récupérer les stats du mois précédent
        // Pour l'exemple, nous simulons
        $lastMonthStats = [
            'total' => rand(2, 8),
            'pending' => rand(0, 3),
            'interview' => rand(0, 2),
            'accepted' => rand(0, 1),
            'rejected' => rand(0, 3)
        ];

        return [
            'current' => $currentStats,
            'lastMonth' => $lastMonthStats,
            'growth' => [
                'applications' => $currentStats['total'] - $lastMonthStats['total'],
                'interviews' => $currentStats['interview'] - $lastMonthStats['interview'],
                'success' => $currentStats['accepted'] - $lastMonthStats['accepted']
            ]
        ];
    }

    public function getRecentActivity(int $userId, int $limit = 10): array
    {
        $activities = [];

        // Récupérer les candidatures récentes
        $recentApplications = $this->applicationRepository->findByUser($userId);
        foreach (array_slice($recentApplications, 0, $limit) as $application) {
            $activities[] = [
                'type' => 'application',
                'action' => 'created',
                'title' => 'Candidature envoyée',
                'description' => "Candidature pour {$application->getPosition()} chez {$application->getCompany()}",
                'date' => $application->getCreatedAt(),
                'status' => $application->getStatus(),
                'entity' => [
                    'id' => $application->getId(),
                    'type' => 'application'
                ]
            ];
        }

        // Récupérer les notifications récentes
        $recentNotifications = $this->notificationRepository->findByUser($userId, false);
        foreach (array_slice($recentNotifications, 0, $limit) as $notification) {
            $activities[] = [
                'type' => 'notification',
                'action' => 'received',
                'title' => $notification->getTitle(),
                'description' => $notification->getMessage(),
                'date' => $notification->getCreatedAt(),
                'isRead' => $notification->isRead(),
                'entity' => [
                    'id' => $notification->getId(),
                    'type' => 'notification'
                ]
            ];
        }

        // Récupérer les événements récents
        $recentEvents = $this->scheduleEventRepository->findByUser($userId);
        foreach (array_slice($recentEvents, 0, $limit) as $event) {
            $activities[] = [
                'type' => 'event',
                'action' => 'scheduled',
                'title' => $event->getTitle(),
                'description' => "Événement programmé pour le " . $event->getStartDate()->format('d/m/Y'),
                'date' => $event->getCreatedAt(),
                'eventType' => $event->getType(),
                'entity' => [
                    'id' => $event->getId(),
                    'type' => 'event'
                ]
            ];
        }

        // Trier par date décroissante
        usort($activities, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return array_slice($activities, 0, $limit);
    }

    public function getApplicationSuccess(int $userId): array
    {
        $stats = $this->applicationRepository->getApplicationsStats($userId);
        
        $successRate = 0;
        $interviewRate = 0;
        
        if ($stats['total'] > 0) {
            $successRate = round(($stats['accepted'] / $stats['total']) * 100, 1);
            $interviewRate = round((($stats['interview'] + $stats['accepted']) / $stats['total']) * 100, 1);
        }

        return [
            'successRate' => $successRate,
            'interviewRate' => $interviewRate,
            'totalApplications' => $stats['total'],
            'acceptedApplications' => $stats['accepted'],
            'interviewApplications' => $stats['interview'],
            'breakdown' => $stats
        ];
    }
}
