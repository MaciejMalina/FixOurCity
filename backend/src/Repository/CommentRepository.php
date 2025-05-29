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

    /**
     * Pobiera komentarze z paginacją, filtrowaniem po reportId oraz
     * sortowaniem po dowolnym polu.
     *
     * @param array{reportId?:int} $filters
     */
    public function findFiltered(
        array $filters = [],
        int   $page      = 1,
        int   $limit     = 10,
        string $sortField = 'createdAt',
        string $sortOrder = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.' . $sortField, $sortOrder)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if (!empty($filters['reportId'])) {
            $qb->andWhere('c.report = :reportId')
               ->setParameter('reportId', $filters['reportId']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Zlicza łączną liczbę komentarzy zgodnych z filtrem reportId.
     *
     * @param array{reportId?:int} $filters
     */
    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        if (!empty($filters['reportId'])) {
            $qb->andWhere('c.report = :reportId')
               ->setParameter('reportId', $filters['reportId']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findByReportId(int $reportId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.report = :reportId')
            ->setParameter('reportId', $reportId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
