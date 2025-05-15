<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Pobiera kategorie z paginacją, filtrowaniem po nazwie i sortowaniem.
     *
     * @param array{name?:string} $filters
     */
    public function findFiltered(
        array  $filters   = [],
        int    $page      = 1,
        int    $limit     = 10,
        string $sortField = 'name',
        string $sortOrder = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('c')
                   ->orderBy('c.' . $sortField, $sortOrder)
                   ->setFirstResult(($page - 1) * $limit)
                   ->setMaxResults($limit);

        if (!empty($filters['name'])) {
            $qb->andWhere('c.name LIKE :name')
               ->setParameter('name', '%'.$filters['name'].'%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Zlicza kategorie pasujące do filtra.
     *
     * @param array{name?:string} $filters
     */
    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('c')
                   ->select('COUNT(c.id)');

        if (!empty($filters['name'])) {
            $qb->andWhere('c.name LIKE :name')
               ->setParameter('name', '%'.$filters['name'].'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
