<?php

namespace App\Repository;

use App\Entity\ScheduleEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScheduleEvent>
 */
class ScheduleEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduleEvent::class);
    }

    /**
     * Find events for a user
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('se')
            ->andWhere('se.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('se.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events in a date range
     */
    public function findByDateRange(int $userId, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('se')
            ->andWhere('se.user = :userId')
            ->andWhere('se.startDate >= :start')
            ->andWhere('se.startDate <= :end')
            ->setParameter('userId', $userId)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('se.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find upcoming events
     */
    public function findUpcomingEvents(int $userId, int $limit = 5): array
    {
        return $this->createQueryBuilder('se')
            ->andWhere('se.user = :userId')
            ->andWhere('se.startDate > :now')
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime())
            ->orderBy('se.startDate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events by type
     */
    public function findByType(int $userId, string $type): array
    {
        return $this->createQueryBuilder('se')
            ->andWhere('se.user = :userId')
            ->andWhere('se.type = :type')
            ->setParameter('userId', $userId)
            ->setParameter('type', $type)
            ->orderBy('se.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events for today
     */
    public function findTodayEvents(int $userId): array
    {
        $today = new \DateTime();
        $tomorrow = clone $today;
        $tomorrow->add(new \DateInterval('P1D'));

        return $this->createQueryBuilder('se')
            ->andWhere('se.user = :userId')
            ->andWhere('se.startDate >= :today')
            ->andWhere('se.startDate < :tomorrow')
            ->setParameter('userId', $userId)
            ->setParameter('today', $today->format('Y-m-d 00:00:00'))
            ->setParameter('tomorrow', $tomorrow->format('Y-m-d 00:00:00'))
            ->orderBy('se.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events that conflict with a given time range
     */
    public function findConflictingEvents(int $userId, \DateTimeInterface $start, \DateTimeInterface $end, ?int $excludeId = null): array
    {
        $qb = $this->createQueryBuilder('se')
            ->andWhere('se.user = :userId')
            ->andWhere('se.startDate < :end')
            ->andWhere('se.endDate > :start')
            ->setParameter('userId', $userId)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($excludeId) {
            $qb->andWhere('se.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Count events by type for stats
     */
    public function getEventStats(int $userId): array
    {
        $qb = $this->createQueryBuilder('se');
        
        $result = $qb
            ->select('se.type, COUNT(se.id) as count')
            ->andWhere('se.user = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('se.type')
            ->getQuery()
            ->getResult();

        $stats = [
            'interview' => 0,
            'meeting' => 0,
            'reminder' => 0,
            'deadline' => 0,
            'personal' => 0
        ];

        foreach ($result as $row) {
            if (isset($stats[$row['type']])) {
                $stats[$row['type']] = (int) $row['count'];
            }
        }

        return $stats;
    }

    public function save(ScheduleEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ScheduleEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
