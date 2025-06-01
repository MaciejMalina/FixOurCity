<?php
namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'createdAt',
        string $sortOrder = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('c')
                   ->leftJoin('c.report', 'r')
                   ->addSelect('r');

        if (!empty($filters['reportId'])) {
            $qb->andWhere('r.id = :rId')
               ->setParameter('rId', $filters['reportId']);
        }

        $allowed = ['createdAt'];
        if (!in_array($sortField, $allowed)) {
            $sortField = 'createdAt';
        }
        $order = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('c.' . $sortField, $order)
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('c')
                   ->select('COUNT(c.id)')
                   ->leftJoin('c.report', 'r');

        if (!empty($filters['reportId'])) {
            $qb->andWhere('r.id = :rId')
               ->setParameter('rId', $filters['reportId']);
        }
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
