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

    public function findFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'createdAt',
        string $sortOrder = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('i')
                   ->leftJoin('i.report', 'r')
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

        $qb->orderBy('i.' . $sortField, $order)
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('i')
                   ->select('COUNT(i.id)')
                   ->leftJoin('i.report', 'r');

        if (!empty($filters['reportId'])) {
            $qb->andWhere('r.id = :rId')
               ->setParameter('rId', $filters['reportId']);
        }
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
