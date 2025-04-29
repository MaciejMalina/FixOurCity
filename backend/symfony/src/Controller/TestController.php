<?php

// src/Controller/TestController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class TestController extends AbstractController
{
    #[Route('/test/hash', name: 'test_hash')]
    public function testHash(UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = new User();
        $user->setEmail('test@example.com');
        
        $hashed = $passwordHasher->hashPassword($user, '1234567890');

        return $this->json(['hash' => $hashed]);
    }
}
