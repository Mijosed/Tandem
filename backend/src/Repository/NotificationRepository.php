<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Find notifications for a user
     */
    public function findByUser(int $userId, bool $onlyUnread = false): array
    {
        $qb = $this->createQueryBuilder('n')
            ->andWhere('n.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('n.createdAt', 'DESC');

        if ($onlyUnread) {
            $qb->andWhere('n.isRead = false');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find scheduled notifications that should be sent
     */
    public function findScheduledNotifications(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.scheduledFor <= :date')
            ->andWhere('n.isRead = false')
            ->setParameter('date', $date)
            ->orderBy('n.scheduledFor', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', true)
            ->set('n.updatedAt', ':now')
            ->where('n.user = :userId')
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }

    /**
     * Count unread notifications for a user
     */
    public function countUnread(int $userId): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.user = :userId')
            ->andWhere('n.isRead = false')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find notifications by type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.type = :type')
            ->setParameter('type', $type)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Clean old read notifications
     */
    public function cleanOldNotifications(int $daysOld = 30): int
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . $daysOld . 'D'));

        return $this->createQueryBuilder('n')
            ->delete()
            ->where('n.isRead = true')
            ->andWhere('n.updatedAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }

    public function save(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
