<?php

namespace App\Controller;

use App\Service\UserService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use OpenApi\Attributes as OA;

#[Route('/api/users')]
class UserController extends AbstractController
{
    private UserService $userService;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(UserService $userService, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'user_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users/register',
        summary: 'Rejestracja nowego użytkownika',
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'User created'),
            new OA\Response(response: 400, description: 'Missing required fields')
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'], $data['firstName'], $data['lastName'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        try {
            $user = $this->userService->createUser(
                $data['email'],
                $data['password'],
                $data['firstName'],
                $data['lastName']
            );
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName()
        ], 201);
    }

    #[Route('', name: 'user_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        summary: 'Pobierz listę użytkowników',
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Lista użytkowników')
        ]
    )]
    public function index(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        $userArray = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName()
            ];
        }, $users);

        return $this->json($userArray);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/{id}',
        summary: 'Pobierz szczegóły użytkownika',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Dane użytkownika'),
            new OA\Response(response: 404, description: 'User not found')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => array_values($user->getRoles()),
        ]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/users/{id}',
        summary: 'Usuń użytkownika',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'User deleted successfully'),
            new OA\Response(response: 404, description: 'User not found')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(['message' => 'User deleted successfully']);
    }
}
