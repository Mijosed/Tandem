<?php

namespace App\Repository;

use App\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Application>
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    /**
     * @return Application[] Returns an array of Application objects
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.applicationDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Application[] Returns an array of Application objects
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->setParameter('status', $status)
            ->orderBy('a.applicationDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Application[] Returns applications with upcoming interviews
     */
    public function findUpcomingInterviews(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :userId')
            ->andWhere('a.interviewDate IS NOT NULL')
            ->andWhere('a.interviewDate > :now')
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime())
            ->orderBy('a.interviewDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getApplicationsStats(int $userId): array
    {
        $qb = $this->createQueryBuilder('a');
        
        $result = $qb
            ->select('a.status, COUNT(a.id) as count')
            ->andWhere('a.user = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('a.status')
            ->getQuery()
            ->getResult();

        $stats = [
            'pending' => 0,
            'followed_up' => 0,
            'interview' => 0,
            'rejected' => 0,
            'accepted' => 0,
            'total' => 0
        ];

        foreach ($result as $row) {
            $stats[$row['status']] = (int) $row['count'];
            $stats['total'] += (int) $row['count'];
        }

        return $stats;
    }

    public function save(Application $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Application $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
