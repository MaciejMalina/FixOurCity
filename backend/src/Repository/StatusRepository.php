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

    public function findFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'label',
        string $sortOrder = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('s');
        if (!empty($filters['label'])) {
            $qb->andWhere('s.label ILIKE :lbl')
               ->setParameter('lbl', '%' . $filters['label'] . '%');
        }

        $allowedSortFields = ['label'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'label';
        }
        $order = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('s.' . $sortField, $order)
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countFiltered(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('s')
                   ->select('COUNT(s.id)');
        if (!empty($filters['label'])) {
            $qb->andWhere('s.label ILIKE :lbl')
               ->setParameter('lbl', '%' . $filters['label'] . '%');
        }
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
