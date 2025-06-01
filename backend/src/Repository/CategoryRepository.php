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

    public function findFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'name',
        string $sortOrder = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('c');
        if (!empty($filters['name'])) {
            $qb->andWhere('c.name ILIKE :name')
               ->setParameter('name', '%' . $filters['name'] . '%');
        }

        $allowedSortFields = ['name'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
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
                   ->select('COUNT(c.id)');
        if (!empty($filters['name'])) {
            $qb->andWhere('c.name ILIKE :name')
               ->setParameter('name', '%' . $filters['name'] . '%');
        }
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
