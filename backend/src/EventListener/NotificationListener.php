<?php

namespace App\EventListener;

use App\Entity\Notification;
use App\Entity\User;
use App\Service\NotificationEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::postPersist)]
class NotificationListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationEmailService $emailService
    ) {}

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Notification) {
            return;
        }

        // Si l'utilisateur n'est pas défini mais qu'on a un userId, récupérer l'utilisateur
        if (!$entity->getUser() && $entity->getUserId()) {
            $user = $this->entityManager->getRepository(User::class)->find($entity->getUserId());
            if ($user) {
                $entity->setUser($user);
            }
        }
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Notification) {
            return;
        }

        // Envoyer l'email après que la notification soit créée
        if ($entity->getUser()) {
            try {
                $this->emailService->sendNotificationEmail($entity, $entity->getUser());
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas faire échouer le processus
                error_log('Erreur envoi email notification: ' . $e->getMessage());
            }
        }
    }
}
