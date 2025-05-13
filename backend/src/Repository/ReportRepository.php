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

    public function findFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.status', 's')->addSelect('s')
            ->leftJoin('r.category', 'c')->addSelect('c')
            ->orderBy('r.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if (!empty($filters['status'])) {
            $qb->andWhere('s.label = :status')->setParameter('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('c.name = :category')->setParameter('category', $filters['category']);
        }

        return $qb->getQuery()->getResult();
    }
}
