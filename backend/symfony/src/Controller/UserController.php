<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        $users = [
            ['id' => 1, 'name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            ['id' => 2, 'name' => 'Anna Nowak', 'email' => 'anna@example.com'],
        ];
        return $this->json($users);
    }

    #[Route('/api/users/{id}', name: 'get_user_by_id', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $users = [
            1 => ['id' => 1, 'name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            2 => ['id' => 2, 'name' => 'Anna Nowak', 'email' => 'anna@example.com'],
        ];
        if (!isset($users[$id])) {
            return $this->json(['error' => 'User not found'], 404);
        }
        return $this->json($users[$id]);
    }

    #[Route('/api/users', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['name']) || !isset($data['email'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }
        $newUser = [
            'id' => rand(3, 1000),
            'name' => $data['name'],
            'email' => $data['email']
        ];
        return $this->json(['message' => 'User created successfully', 'user' => $newUser], 201);
    }

    #[Route('/api/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['name']) && !isset($data['email'])) {
            return $this->json(['error' => 'No data to update'], 400);
        }
        $updatedUser = [
            'id' => $id,
            'name' => $data['name'] ?? 'Updated Name',
            'email' => $data['email'] ?? 'updated@example.com'
        ];
        return $this->json(['message' => 'User updated successfully', 'user' => $updatedUser], 200);
    }

    #[Route('/api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        return $this->json(['message' => 'User deleted successfully', 'id' => $id], 200);
    }
}
