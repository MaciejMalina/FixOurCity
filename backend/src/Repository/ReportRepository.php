<?php

namespace App\Repository;

use App\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * @param array{status?:string,category?:string} $filters
     */
    public function findFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'createdAt',
        string $sortOrder = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.status',   's')->addSelect('s')
            ->leftJoin('r.category', 'c')->addSelect('c')
            ->orderBy('r.' . $sortField, $sortOrder)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if (!empty($filters['status'])) {
            $qb->andWhere('s.label = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('c.name = :category')
               ->setParameter('category', $filters['category']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array{status?:string,category?:string} $filters
     */
    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->leftJoin('r.status',   's')
            ->leftJoin('r.category', 'c');

        if (!empty($filters['status'])) {
            $qb->andWhere('s.label = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('c.name = :category')
               ->setParameter('category', $filters['category']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
