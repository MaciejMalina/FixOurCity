<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function createUser(string $email, string $plainPassword, string $firstName, string $lastName): User
    {
        if (empty($email) || empty($plainPassword) || empty($firstName) || empty($lastName)) {
            throw new \InvalidArgumentException('Missing required fields.');
        }
    
        if ($this->userRepository->findOneBy(['email' => $email])) {
            throw new \RuntimeException('User with this email already exists.');
        }
    
        $user = new User();
        $user->setEmail($email);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        dump($hashedPassword);
        $user->setPassword($hashedPassword);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles(['ROLE_USER']);
    
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    
        return $user;
    }

}
