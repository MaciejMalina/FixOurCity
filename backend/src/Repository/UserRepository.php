<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findPaginated(array $filters = [], array $sort = ['u.id' => 'ASC'], int $page = 1, int $limit = 10): Paginator
    {
        $qb = $this->createQueryBuilder('u');

        if (array_key_exists('approved', $filters)) {
            $qb->andWhere('u.approved = :approved')
            ->setParameter('approved', (bool)$filters['approved']);
        }

        if (!empty($filters['email'])) {
            $qb->andWhere('u.email LIKE :email')
               ->setParameter('email', '%'.$filters['email'].'%');
        }
        if (!empty($filters['firstName'])) {
            $qb->andWhere('u.firstName LIKE :firstName')
               ->setParameter('firstName', '%'.$filters['firstName'].'%');
        }
        if (!empty($filters['lastName'])) {
            $qb->andWhere('u.lastName LIKE :lastName')
               ->setParameter('lastName', '%'.$filters['lastName'].'%');
        }

        foreach ($sort as $field => $direction) {
            $qb->addOrderBy($field, strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC');
        }

        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        return new Paginator($qb);
    }
}
