<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Candidature;
use App\Entity\Notification;
use App\Entity\ScheduleEvent;
use App\Repository\CandidatureRepository;
use App\Repository\NotificationRepository;
use App\Repository\ScheduleEventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboard', name: 'api_dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CandidatureRepository $candidatureRepository,
        private NotificationRepository $notificationRepository,
        private ScheduleEventRepository $scheduleEventRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('/stats/{userId}', name: 'stats', methods: ['GET'])]
    public function getStats(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        // Statistiques des candidatures
        $candidatureStats = $this->candidatureRepository->getCandidaturesStats($userId);
        
        // Prochains entretiens
        $upcomingInterviews = $this->candidatureRepository->findUpcomingInterviews($userId);
        
        // Notifications non lues
        $unreadNotifications = $this->notificationRepository->countUnread($userId);
        
        // Événements aujourd'hui
        $todayEvents = $this->scheduleEventRepository->findTodayEvents($userId);
        
        // Événements à venir
        $upcomingEvents = $this->scheduleEventRepository->findUpcomingEvents($userId, 5);

        // Calcul du taux de réponse (simulation)
        $responseRate = 0;
        if ($candidatureStats['total'] > 0) {
            $responses = $candidatureStats['interview'] + $candidatureStats['accepted'] + $candidatureStats['rejected'];
            $responseRate = round(($responses / $candidatureStats['total']) * 100, 1);
        }

        return $this->json([
            'Candidatures' => $candidatureStats['total'],
            'upcomingInterviews' => count($upcomingInterviews),
            'messages' => $unreadNotifications,
            'responseRate' => $responseRate,
            'pendingResponses' => $candidatureStats['pending'] + $candidatureStats['followed_up'],
            'todayEvents' => count($todayEvents),
            'upcomingEvents' => array_map(function($event) {
                return [
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'startDate' => $event->getStartDate()->format('Y-m-d H:i:s'),
                    'type' => $event->getType()
                ];
            }, $upcomingEvents),
            'CandidaturesByStatus' => $candidatureStats,
            'recentInterviews' => array_map(function($Candidature) {
                return [
                    'id' => $Candidature->getId(),
                    'position' => $Candidature->getPosition(),
                    'company' => $Candidature->getCompany(),
                    'interviewDate' => $Candidature->getInterviewDate()?->format('Y-m-d H:i:s'),
                    'status' => $Candidature->getStatus()
                ];
            }, array_slice($upcomingInterviews, 0, 3))
        ]);
    }

    #[Route('/recent-activity/{userId}', name: 'recent_activity', methods: ['GET'])]
    public function getRecentActivity(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        // Récupérer les activités récentes
        $recentCandidatures = $this->candidatureRepository->findByUser($userId);
        $recentNotifications = $this->notificationRepository->findByUser($userId, false);
        $recentEvents = $this->scheduleEventRepository->findByUser($userId);

        // Limiter aux 10 plus récents
        $recentCandidatures = array_slice($recentCandidatures, 0, 5);
        $recentNotifications = array_slice($recentNotifications, 0, 5);
        $recentEvents = array_slice($recentEvents, 0, 5);

        return $this->json([
            'Candidatures' => array_map(function($app) {
                return [
                    'id' => $app->getId(),
                    'position' => $app->getPosition(),
                    'company' => $app->getCompany(),
                    'status' => $app->getStatus(),
                    'CandidatureDate' => $app->getCandidatureDate()->format('Y-m-d'),
                    'updatedAt' => $app->getUpdatedAt()->format('Y-m-d H:i:s')
                ];
            }, $recentCandidatures),
            'notifications' => array_map(function($notif) {
                return [
                    'id' => $notif->getId(),
                    'title' => $notif->getTitle(),
                    'message' => $notif->getMessage(),
                    'type' => $notif->getType(),
                    'isRead' => $notif->isRead(),
                    'createdAt' => $notif->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }, $recentNotifications),
            'events' => array_map(function($event) {
                return [
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'type' => $event->getType(),
                    'startDate' => $event->getStartDate()->format('Y-m-d H:i:s'),
                    'endDate' => $event->getEndDate()->format('Y-m-d H:i:s')
                ];
            }, $recentEvents)
        ]);
    }

    #[Route('/quick-actions/{userId}', name: 'quick_actions', methods: ['POST'])]
    public function quickActions(int $userId, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $action = $data['action'] ?? null;

        switch ($action) {
            case 'quick_Candidature':
                return $this->createQuickCandidature($user, $data);
            case 'quick_reminder':
                return $this->createQuickReminder($user, $data);
            case 'mark_notifications_read':
                return $this->markNotificationsAsRead($userId);
            default:
                return $this->json(['error' => 'Action non reconnue'], 400);
        }
    }

    private function createQuickCandidature(User $user, array $data): JsonResponse
    {
        if (!isset($data['position'], $data['company'])) {
            return $this->json(['error' => 'Position et entreprise requises'], 400);
        }

        $Candidature = new Candidature();
        $Candidature->setUser($user);
        $Candidature->setPosition($data['position']);
        $Candidature->setCompany($data['company']);
        $Candidature->setCandidatureDate(new \DateTime());
        $Candidature->setStatus('pending');

        $this->entityManager->persist($Candidature);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Candidature créée avec succès',
            'Candidature' => [
                'id' => $Candidature->getId(),
                'position' => $Candidature->getPosition(),
                'company' => $Candidature->getCompany(),
                'status' => $Candidature->getStatus(),
                'CandidatureDate' => $Candidature->getCandidatureDate()->format('Y-m-d')
            ]
        ]);
    }

    private function createQuickReminder(User $user, array $data): JsonResponse
    {
        if (!isset($data['title'], $data['scheduledFor'])) {
            return $this->json(['error' => 'Titre et date requises'], 400);
        }

        try {
            $scheduledFor = new \DateTime($data['scheduledFor']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Format de date invalide'], 400);
        }

        $notification = new Notification();
        $notification->setUser($user);
        $notification->setTitle($data['title']);
        $notification->setMessage($data['message'] ?? '');
        $notification->setType('reminder');
        $notification->setScheduledFor($scheduledFor);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Rappel créé avec succès',
            'notification' => [
                'id' => $notification->getId(),
                'title' => $notification->getTitle(),
                'scheduledFor' => $notification->getScheduledFor()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    private function markNotificationsAsRead(int $userId): JsonResponse
    {
        $this->notificationRepository->markAllAsRead($userId);

        return $this->json(['message' => 'Toutes les notifications ont été marquées comme lues']);
    }
}
