<?php

namespace App\Repository;

use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    /**
     * Pobiera statusy z paginacją, filtrowaniem po nazwie i sortowaniem.
     *
     * @param array{label?:string} $filters
     */
    public function findFiltered(
        array  $filters   = [],
        int    $page      = 1,
        int    $limit     = 10,
        string $sortField = 'label',
        string $sortOrder = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('s')
                   ->orderBy('s.' . $sortField, $sortOrder)
                   ->setFirstResult(($page - 1) * $limit)
                   ->setMaxResults($limit);

        if (!empty($filters['label'])) {
            $qb->andWhere('s.label LIKE :label')
               ->setParameter('label', '%'.$filters['label'].'%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Zlicza statusy zgodne z filtrem.
     *
     * @param array{label?:string} $filters
     */
    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('s')
                   ->select('COUNT(s.id)');

        if (!empty($filters['label'])) {
            $qb->andWhere('s.label LIKE :label')
               ->setParameter('label', '%'.$filters['label'].'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
