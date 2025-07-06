<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    #[Route('', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getNotifications(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);
        
        $notifications = $this->notificationService->getNotificationsForUser($user, $limit, $offset);
        
        $data = array_map(function ($notification) {
            return [
                'id' => $notification->getId(),
                'title' => $notification->getTitle(),
                'message' => $notification->getMessage(),
                'type' => $notification->getType(),
                'read' => $notification->isRead(),
                'createdAt' => $notification->getCreatedAt()->format('c'),
                'data' => $notification->getData()
            ];
        }, $notifications);
        
        return $this->json([
            'notifications' => $data,
            'total' => count($data)
        ]);
    }

    #[Route('/unread-count', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getUnreadCount(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $count = $this->notificationService->getUnreadCountForUser($user);
        
        return $this->json(['count' => $count]);
    }

    #[Route('/{id}/read', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function markAsRead(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $notification = $this->notificationService->markAsRead($id, $user);
        
        if (!$notification) {
            return $this->json(['error' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'id' => $notification->getId(),
            'read' => $notification->isRead()
        ]);
    }

    #[Route('/mark-all-read', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function markAllAsRead(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $count = $this->notificationService->markAllAsRead($user);
        
        return $this->json(['count' => $count]);
    }

    #[Route('/test', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createTestNotification(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $data = json_decode($request->getContent(), true);
        
        $notification = $this->notificationService->createNotification(
            $user,
            $data['title'] ?? 'Test Notification',
            $data['message'] ?? 'This is a test notification',
            $data['type'] ?? 'info',
            $data['data'] ?? []
        );
        
        return $this->json([
            'id' => $notification->getId(),
            'title' => $notification->getTitle(),
            'message' => $notification->getMessage(),
            'type' => $notification->getType(),
            'read' => $notification->isRead(),
            'createdAt' => $notification->getCreatedAt()->format('c')
        ], Response::HTTP_CREATED);
    }
} 