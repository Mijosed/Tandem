<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

class NotificationService
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private HubInterface $hub,
        private SerializerInterface $serializer
    ) {
    }

    public function createNotification(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        array $data = []
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setType($type);
        $notification->setData($data);

        $this->notificationRepository->save($notification, true);

        // Publier la notification via Mercure
        $this->publishNotification($notification);

        return $notification;
    }

    public function publishNotification(Notification $notification): void
    {
        $update = new Update(
            sprintf('notifications/user/%d', $notification->getUser()->getId()),
            $this->serializer->serialize([
                'id' => $notification->getId(),
                'title' => $notification->getTitle(),
                'message' => $notification->getMessage(),
                'type' => $notification->getType(),
                'read' => $notification->isRead(),
                'createdAt' => $notification->getCreatedAt()->format('c'),
                'data' => $notification->getData()
            ], 'json')
        );

        $this->hub->publish($update);
    }

    public function markAsRead(int $notificationId, User $user): ?Notification
    {
        $notification = $this->notificationRepository->find($notificationId);
        
        if (!$notification || $notification->getUser()->getId() !== $user->getId()) {
            return null;
        }

        $notification->setRead(true);
        $this->notificationRepository->save($notification, true);

        // Publier la mise à jour via Mercure
        $this->publishNotification($notification);

        return $notification;
    }

    public function markAllAsRead(User $user): int
    {
        $count = $this->notificationRepository->markAllAsReadByUser($user->getId());
        
        // Publier la mise à jour via Mercure
        $update = new Update(
            sprintf('notifications/user/%d/read-all', $user->getId()),
            json_encode(['count' => $count])
        );
        
        $this->hub->publish($update);

        return $count;
    }

    public function getNotificationsForUser(User $user, int $limit = 50, int $offset = 0): array
    {
        return $this->notificationRepository->findByUser($user->getId(), $limit, $offset);
    }

    public function getUnreadCountForUser(User $user): int
    {
        return $this->notificationRepository->countUnreadByUser($user->getId());
    }
} 