<?php

namespace App\Repository;

use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function findByUser(int $userId): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find subscriptions that need to be renewed
     */
    public function findExpiringSoon(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->andWhere('s.nextBilling <= :date')
            ->setParameter('status', 'active')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find active premium subscriptions
     */
    public function findActivePremium(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.plan = :plan')
            ->andWhere('s.status = :status')
            ->setParameter('plan', 'premium')
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count subscriptions by plan
     */
    public function getSubscriptionStats(): array
    {
        $qb = $this->createQueryBuilder('s');
        
        $result = $qb
            ->select('s.plan, s.status, COUNT(s.id) as count')
            ->groupBy('s.plan, s.status')
            ->getQuery()
            ->getResult();

        $stats = [
            'free' => ['active' => 0, 'inactive' => 0],
            'premium' => ['active' => 0, 'inactive' => 0, 'cancelled' => 0, 'expired' => 0]
        ];

        foreach ($result as $row) {
            if (isset($stats[$row['plan']][$row['status']])) {
                $stats[$row['plan']][$row['status']] = (int) $row['count'];
            }
        }

        return $stats;
    }

    public function save(Subscription $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Subscription $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
