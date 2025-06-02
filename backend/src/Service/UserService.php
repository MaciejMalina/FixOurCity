<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function list(
        array $filters = [],
        array $sort = ['u.id' => 'ASC'],
        int $page = 1,
        int $limit = 10
    ): \Doctrine\ORM\Tools\Pagination\Paginator {
        return $this->userRepository->findPaginated($filters, $sort, $page, $limit);
    }

    public function create(array $data): User
    {
        $user = new User();
        $user->setEmail($data['email']);
        $hashed = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashed);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        if (!empty($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $this->em->persist($user);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new \InvalidArgumentException('Podany email jest już w użyciu.');
        }

        return $user;
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['password'])) {
            $hashed = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashed);
        }
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $this->em->flush();
        return $user;
    }

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
