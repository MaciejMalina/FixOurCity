<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/api/v1/users')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    public function __construct(private UserService $userService, private UserRepository $repo) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $filters = array_filter([
            'email'     => $request->query->get('email'),
            'firstName' => $request->query->get('firstName'),
            'lastName'  => $request->query->get('lastName'),
        ]);
        $sort = $request->query->get('sort', 'u.id:ASC');
        $parts = explode(':', $sort);
        $field = $parts[0] ?? 'u.id';
        $dir   = $parts[1] ?? 'ASC';
        $page  = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', 10);

        $paginator = $this->userService->list(
            $filters,
            [$field => $dir],
            $page,
            $limit
        );

        $total = count($paginator);
        $users = [];
        foreach ($paginator as $u) {
            $users[] = [
                'id'        => $u->getId(),
                'email'     => $u->getEmail(),
                'firstName' => $u->getFirstName(),
                'lastName'  => $u->getLastName(),
                'roles'     => $u->getRoles(),
            ];
        }

        return $this->json([
            'data' => $users,
            'meta' => [
                'page'  => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int)ceil($total/$limit),
            ]
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $user = $this->userService->create($payload);

        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName'  => $user->getLastName(),
            'roles'     => $user->getRoles(),
        ], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName'  => $user->getLastName(),
            'roles'     => $user->getRoles(),
        ]);
    }

    #[Route('/{id}', methods: ['PUT','PATCH'])]
    public function update(User $user, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $user = $this->userService->update($user, $payload);

        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName'  => $user->getLastName(),
            'roles'     => $user->getRoles(),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->userService->delete($user);
        return $this->json(null, 204);
    }
}
