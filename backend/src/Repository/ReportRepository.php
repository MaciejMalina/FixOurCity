<?php
namespace App\Repository;

use App\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    public function findFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'createdAt',
        string $sortOrder = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('r')
                   ->addSelect('c')
                   ->addSelect('s')
                   ->leftJoin('r.category', 'c')
                   ->leftJoin('r.status', 's');

        if (!empty($filters['category'])) {
            $qb->andWhere('c.id = :catId')
               ->setParameter('catId', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('s.id = :statId')
               ->setParameter('statId', $filters['status']);
        }

        if (!empty($filters['title'])) {
            $qb->andWhere('r.title ILIKE :title')
               ->setParameter('title', '%' . $filters['title'] . '%');
        }

        $allowedSortFields = ['createdAt','title'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'createdAt';
        }

        $order = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('r.'.$sortField, $order)
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('r')
                   ->select('COUNT(r.id)')
                   ->leftJoin('r.category', 'c')
                   ->leftJoin('r.status', 's');

        if (!empty($filters['category'])) {
            $qb->andWhere('c.id = :catId')
               ->setParameter('catId', $filters['category']);
        }
        if (!empty($filters['status'])) {
            $qb->andWhere('s.id = :statId')
               ->setParameter('statId', $filters['status']);
        }
        if (!empty($filters['title'])) {
            $qb->andWhere('r.title ILIKE :title')
               ->setParameter('title', '%' . $filters['title'] . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
