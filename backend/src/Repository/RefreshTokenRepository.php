<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $r)
    {
        parent::__construct($r, RefreshToken::class);
    }

    public function findValid(string $token): ?RefreshToken
    {
        $now = new \DateTimeImmutable();
        return $this->createQueryBuilder('rt')
            ->andWhere('rt.token = :t')->setParameter('t',$token)
            ->andWhere('rt.expiresAt > :now')->setParameter('now',$now)
            ->getQuery()->getOneOrNullResult();
    }

    public function revoke(RefreshToken $rt): void
    {
        $em = $this->getEntityManager();
        $em->remove($rt);
        $em->flush();
    }
}
