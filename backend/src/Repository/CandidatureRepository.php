<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Candidature>
 */
class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    /**
     * @return Candidature[] Returns an array of Candidature objects
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.CandidatureDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Candidature[] Returns an array of Candidature objects
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->setParameter('status', $status)
            ->orderBy('a.CandidatureDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Candidature[] Returns Candidatures with upcoming interviews
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

    public function getCandidaturesStats(int $userId): array
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

    /**
     * Find a candidature by user and job ID
     */
    public function findByUserAndJobId(\App\Entity\User $user, string $jobId): ?Candidature
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.jobId = :jobId')
            ->setParameter('user', $user)
            ->setParameter('jobId', $jobId)
            ->orderBy('c.dateCreation', 'DESC') // Plus récent en premier
            ->setMaxResults(1) // Seulement le premier résultat
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Candidature $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Candidature $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
