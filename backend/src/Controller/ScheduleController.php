<?php

namespace App\Controller;

use App\Entity\ScheduleEvent;
use App\Entity\User;
use App\Service\ScheduleEventService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/schedule', name: 'api_schedule_')]
class ScheduleController extends AbstractController
{
    public function __construct(
        private ScheduleEventService $scheduleEventService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/events/user/{userId}', name: 'events_by_user', methods: ['GET'])]
    public function getUserEvents(int $userId): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $events = $this->scheduleEventService->getUserEvents($user);
        
        return $this->json([
            'hydra:member' => array_map([$this, 'serializeEvent'], $events),
            'hydra:totalItems' => count($events)
        ]);
    }

    #[Route('/events/today/{userId}', name: 'today_events', methods: ['GET'])]
    public function getTodayEvents(int $userId): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $events = $this->scheduleEventService->getTodayEvents($user);
        
        return $this->json([
            'hydra:member' => array_map([$this, 'serializeEvent'], $events),
            'hydra:totalItems' => count($events)
        ]);
    }

    #[Route('/events/upcoming/{userId}', name: 'upcoming_events', methods: ['GET'])]
    public function getUpcomingEvents(int $userId, Request $request): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $limit = $request->query->getInt('limit', 5);
        $events = $this->scheduleEventService->getUpcomingEvents($user, $limit);
        
        return $this->json([
            'hydra:member' => array_map([$this, 'serializeEvent'], $events),
            'hydra:totalItems' => count($events)
        ]);
    }

    #[Route('/events', name: 'create_event', methods: ['POST'])]
    public function createEvent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['title'], $data['startDate'], $data['endDate'], $data['userId'])) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->find($data['userId']);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        try {
            $startDate = new \DateTime($data['startDate']);
            $endDate = new \DateTime($data['endDate']);

            $event = $this->scheduleEventService->createScheduleEvent(
                user: $user,
                title: $data['title'],
                startDate: $startDate,
                endDate: $endDate,
                type: $data['type'] ?? 'personal',
                description: $data['description'] ?? null,
                location: $data['location'] ?? null,
                allDay: $data['allDay'] ?? false
            );

            return $this->json($this->serializeEvent($event), 201);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la création: ' . $e->getMessage()], 400);
        }
    }

    #[Route('/stats/{userId}', name: 'user_stats', methods: ['GET'])]
    public function getUserStats(int $userId): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $allEvents = $this->scheduleEventService->getUserEvents($user);
        $todayEvents = $this->scheduleEventService->getTodayEvents($user);
        $upcomingEvents = $this->scheduleEventService->getUpcomingEvents($user);

        $byType = [];
        foreach ($allEvents as $event) {
            $type = $event->getType();
            $byType[$type] = ($byType[$type] ?? 0) + 1;
        }

        return $this->json([
            'total' => count($allEvents),
            'today' => count($todayEvents),
            'upcoming' => count($upcomingEvents),
            'byType' => $byType
        ]);
    }

    /**
     * Sérialise un événement pour l'API
     */
    private function serializeEvent(ScheduleEvent $event): array
    {
        return [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'startDate' => $event->getStartDate()->format('Y-m-d\TH:i:s'),
            'endDate' => $event->getEndDate()->format('Y-m-d\TH:i:s'),
            'type' => $event->getType(),
            'location' => $event->getLocation(),
            'allDay' => $event->isAllDay(),
            'color' => $event->getColor(),
            'user' => [
                'id' => $event->getUser()->getId(),
                'email' => $event->getUser()->getEmail()
            ],
            'candidature' => $event->getCandidature() ? [
                'id' => $event->getCandidature()->getId(),
                'poste' => $event->getCandidature()->getTitrePoste(),
                'entreprise' => $event->getCandidature()->getEntreprise()
            ] : null,
            'createdAt' => $event->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $event->getUpdatedAt()->format('Y-m-d H:i:s')
        ];
    }
}
