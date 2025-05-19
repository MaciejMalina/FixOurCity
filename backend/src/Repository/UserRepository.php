<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Pobiera stronę użytkowników, z opcjonalnym filtrem po emailu/imię/nazwisko,
     * dowolnym sortowaniem i paginacją.
     *
     * @param array $filters  ['email' => string, 'firstName' => string, 'lastName' => string]
     * @param array $sort     ['u.email' => 'ASC', 'u.createdAt' => 'DESC', …]
     * @param int   $page     numer strony (1-based)
     * @param int   $limit    liczba rekordów na stronę
     * @return Paginator
     */
    public function findPaginated(
        array $filters = [],
        array $sort = ['u.id' => 'ASC'],
        int $page = 1,
        int $limit = 10
    ): Paginator {
        $qb = $this->createQueryBuilder('u');

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
