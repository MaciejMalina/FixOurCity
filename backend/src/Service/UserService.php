<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\SendWelcomeEmailMessage;
use App\Message\UserApprovedEmailMessage;
use App\Message\AdminNewUserRegisteredMessage;


class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private MessageBusInterface $bus,
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

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $user->setApproved(true);
        } else {
            $user->setApproved(false);
        }

        $this->em->persist($user);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new \InvalidArgumentException('Podany email jest już w użyciu.');
        }
        
        $this->bus->dispatch(
           new SendWelcomeEmailMessage($user->getEmail(), $user->getFirstName())
        );
        $this->bus->dispatch(
            new AdminNewUserRegisteredMessage(
                $user->getId(),
                $user->getEmail(),
                $user->getFirstName(),
                $user->getLastName()
            )
        );
        return $user;
    }

    public function approve(User $user, User $admin): User
    {
        if ($user->isApproved()) {
            return $user;
        }
        $user->setApproved(true)
             ->setApprovedAt(new \DateTimeImmutable())
             ->setApprovedBy($admin);
        $this->em->flush();
        return $user;
    }

    public function unapprove(User $user): User
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $user;
        }

        $user->setApproved(false)
            ->setApprovedAt(null)
            ->setApprovedBy(null);
        $this->em->flush();
        return $user;
    }

    public function update(User $user, array $data, User $actor): User
    {
        $wasApproved = $user->isApproved();

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

            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $user->setApproved(true);
            }
        }

        if (array_key_exists('approved', $data)) {
            $actorIsAdmin = in_array('ROLE_ADMIN', $actor->getRoles(), true);
            if (!$actorIsAdmin) {
                throw new AccessDeniedException('Tylko admini mogą zmienić status.');
            }

            if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $user->setApproved((bool)$data['approved']);
            } else {
                $user->setApproved(true);
            }
        }

        $this->em->flush();

        if (!$wasApproved && $user->isApproved()) {
            $this->bus->dispatch(
                new UserApprovedEmailMessage(
                    $user->getEmail(),
                    $user->getFirstName()
                )
            );
        }
        return $user;
    }

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
