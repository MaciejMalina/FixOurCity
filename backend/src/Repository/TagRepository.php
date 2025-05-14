<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Pobiera tagi z paginacją i opcjonalnym filtrowaniem po nazwie.
     *
     * @param array{ name?: string } $filters
     */
    public function findFiltered(
        array  $filters   = [],
        int    $page      = 1,
        int    $limit     = 10,
        string $sortField = 'name',
        string $sortOrder = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.' . $sortField, $sortOrder)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if (!empty($filters['name'])) {
            $qb->andWhere('t.name LIKE :name')
               ->setParameter('name', '%'.$filters['name'].'%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Zlicza wszystkie tagi pasujące do filtra.
     *
     * @param array{ name?: string } $filters
     */
    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)');

        if (!empty($filters['name'])) {
            $qb->andWhere('t.name LIKE :name')
               ->setParameter('name', '%'.$filters['name'].'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
