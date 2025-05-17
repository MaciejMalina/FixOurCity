<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    /**
     * Pobiera obrazy z paginacją, filtrowaniem po reportId i sortowaniem.
     *
     * @param array{reportId?:int} $filters
     */
    public function findFiltered(
        array  $filters    = [],
        int    $page       = 1,
        int    $limit      = 10,
        string $sortField  = 'createdAt',
        string $sortOrder  = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('i')
                   ->orderBy('i.' . $sortField, $sortOrder)
                   ->setFirstResult(($page - 1) * $limit)
                   ->setMaxResults($limit);

        if (!empty($filters['reportId'])) {
            $qb->andWhere('i.report = :reportId')
               ->setParameter('reportId', $filters['reportId']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Zlicza obrazy pasujące do filtra.
     *
     * @param array{reportId?:int} $filters
     */
    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('i')
                   ->select('COUNT(i.id)');

        if (!empty($filters['reportId'])) {
            $qb->andWhere('i.report = :reportId')
               ->setParameter('reportId', $filters['reportId']);
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
