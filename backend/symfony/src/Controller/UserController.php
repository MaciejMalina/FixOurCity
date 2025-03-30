<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $users = $em->getRepository(User::class)->findAll();
        $json = $serializer->serialize($users, 'json', ['groups' => 'user:read']);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/api/users/{id}', name: 'get_user_by_id', methods: ['GET'])]
    public function getUserById(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ], 200);
    }

    #[Route('/api/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Email already registered'], 409);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'User registered successfully'], 201);
    }


    #[Route('/api/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $em->flush();

        return $this->json(['message' => 'User updated successfully'], 200);
    }

    #[Route('/api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'User deleted successfully'], 200);
    }

    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json(['error' => 'Missing credentials'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        return $this->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail()
            ]
        ], 200);
    }

}
