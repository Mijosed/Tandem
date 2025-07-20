<?php

namespace App\EventListener;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::prePersist)]
class NotificationListener
{
    public function __construct(
        private EntityManagerInterface $entityManager
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
}
