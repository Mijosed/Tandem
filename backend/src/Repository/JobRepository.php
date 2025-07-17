<?php

namespace App\Repository;

use App\Entity\Job;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Job>
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    /**
     * Search jobs by title, company, or location
     */
    public function searchJobs(string $query, bool $premiumOnly = false): array
    {
        $qb = $this->createQueryBuilder('j')
            ->where('j.isActive = true')
            ->andWhere('j.title LIKE :query OR j.company LIKE :query OR j.location LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('j.postedDate', 'DESC');

        if ($premiumOnly) {
            $qb->andWhere('j.isPremium = true');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get recent jobs
     */
    public function findRecentJobs(int $limit = 10, bool $premiumOnly = false): array
    {
        $qb = $this->createQueryBuilder('j')
            ->where('j.isActive = true')
            ->orderBy('j.postedDate', 'DESC')
            ->setMaxResults($limit);

        if ($premiumOnly) {
            $qb->andWhere('j.isPremium = true');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Filter jobs by type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.type = :type')
            ->andWhere('j.isActive = true')
            ->setParameter('type', $type)
            ->orderBy('j.postedDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filter jobs by location
     */
    public function findByLocation(string $location): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.location LIKE :location')
            ->andWhere('j.isActive = true')
            ->setParameter('location', '%' . $location . '%')
            ->orderBy('j.postedDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Advanced search with multiple filters
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('j')
            ->where('j.isActive = true');

        if (!empty($filters['query'])) {
            $qb->andWhere('j.title LIKE :query OR j.company LIKE :query OR j.location LIKE :query')
               ->setParameter('query', '%' . $filters['query'] . '%');
        }

        if (!empty($filters['type'])) {
            $qb->andWhere('j.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['location'])) {
            $qb->andWhere('j.location LIKE :location')
               ->setParameter('location', '%' . $filters['location'] . '%');
        }

        if (!empty($filters['company'])) {
            $qb->andWhere('j.company LIKE :company')
               ->setParameter('company', '%' . $filters['company'] . '%');
        }

        if (isset($filters['premiumOnly']) && $filters['premiumOnly']) {
            $qb->andWhere('j.isPremium = true');
        }

        return $qb->orderBy('j.postedDate', 'DESC')->getQuery()->getResult();
    }

    public function save(Job $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Job $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
