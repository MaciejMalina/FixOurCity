<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserManagementController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'admin_user_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/users',
        summary: 'Lista użytkowników',
        description: 'Zwraca listę wszystkich użytkowników. Dostępne tylko dla ADMIN.',
        tags: ['Admin Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista użytkowników',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'email', type: 'string'),
                            new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'firstName', type: 'string'),
                            new OA\Property(property: 'lastName', type: 'string'),
                        ]
                    )
                )
            ),
            new OA\Response(response: 403, description: 'Forbidden')
        ]
    )]
    public function listUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        $data = array_map(fn(User $user) => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ], $users);

        return $this->json($data);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/admin/users/{id}/delete',
        summary: 'Usuń użytkownika',
        description: 'Usuwa użytkownika o podanym ID. Dostępne tylko dla ADMIN.',
        tags: ['Admin Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID użytkownika', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Użytkownik usunięty'),
            new OA\Response(response: 404, description: 'Użytkownik nie znaleziony'),
            new OA\Response(response: 403, description: 'Forbidden')
        ]
    )]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(['message' => 'User deleted successfully']);
    }

    #[Route('/{id}/role', name: 'admin_user_role_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/admin/users/{id}/role',
        summary: 'Zaktualizuj role użytkownika',
        description: 'Zmienia przypisane role dla użytkownika. Dostępne tylko dla ADMIN.',
        tags: ['Admin Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER', 'ROLE_ADMIN'])
                ]
            )
        ),
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID użytkownika', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Role zaktualizowane'),
            new OA\Response(response: 400, description: 'Niepoprawne dane'),
            new OA\Response(response: 404, description: 'Użytkownik nie znaleziony'),
            new OA\Response(response: 403, description: 'Forbidden')
        ]
    )]
    public function updateUserRole(int $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            return $this->json(['error' => 'Roles must be provided as an array'], 400);
        }

        $user->setRoles($data['roles']);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Roles updated successfully',
            'roles' => $user->getRoles()
        ]);
    }
}
