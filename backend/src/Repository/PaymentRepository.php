<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * Find payments for a user
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find payments by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', $status)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find completed payments for a user
     */
    public function findCompletedPayments(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :userId')
            ->andWhere('p.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'completed')
            ->orderBy('p.paidAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find payment by Stripe Payment Intent ID
     */
    public function findByStripePaymentIntentId(string $stripePaymentIntentId): ?Payment
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stripePaymentIntentId = :stripePaymentIntentId')
            ->setParameter('stripePaymentIntentId', $stripePaymentIntentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Calculate total payments for a user
     */
    public function getTotalPayments(int $userId): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->andWhere('p.user = :userId')
            ->andWhere('p.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0.0;
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats(): array
    {
        $qb = $this->createQueryBuilder('p');
        
        $statusResult = $qb
            ->select('p.status, COUNT(p.id) as count, SUM(p.amount) as total')
            ->groupBy('p.status')
            ->getQuery()
            ->getResult();

        $stats = [
            'pending' => ['count' => 0, 'total' => 0],
            'completed' => ['count' => 0, 'total' => 0],
            'failed' => ['count' => 0, 'total' => 0],
            'refunded' => ['count' => 0, 'total' => 0],
            'cancelled' => ['count' => 0, 'total' => 0]
        ];

        foreach ($statusResult as $row) {
            if (isset($stats[$row['status']])) {
                $stats[$row['status']] = [
                    'count' => (int) $row['count'],
                    'total' => (float) $row['total']
                ];
            }
        }

        return $stats;
    }

    /**
     * Get monthly revenue
     */
    public function getMonthlyRevenue(int $year): array
    {
        $qb = $this->createQueryBuilder('p');
        
        $result = $qb
            ->select('MONTH(p.paidAt) as month, SUM(p.amount) as revenue')
            ->andWhere('p.status = :status')
            ->andWhere('YEAR(p.paidAt) = :year')
            ->setParameter('status', 'completed')
            ->setParameter('year', $year)
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->getQuery()
            ->getResult();

        $monthlyRevenue = array_fill(1, 12, 0);
        
        foreach ($result as $row) {
            $monthlyRevenue[(int) $row['month']] = (float) $row['revenue'];
        }

        return $monthlyRevenue;
    }

    public function save(Payment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Payment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
