<?php

namespace App\Controller;

use App\Entity\User as AppUser;
use App\Entity\User;
use App\Service\UserService;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/v1/users')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    public function __construct(private UserService $userService, private UserRepository $repo) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista użytkowników',
        description: 'Zwraca listę użytkowników z paginacją, filtrowaniem i sortowaniem. Dostępne tylko dla admina.',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), example: 10),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'u.id:ASC'),
            new OA\Parameter(name: 'email', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'firstName', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'lastName', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista użytkowników',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@fixourcity.com'),
                                    new OA\Property(property: 'firstName', type: 'string', example: 'Jan'),
                                    new OA\Property(property: 'lastName', type: 'string', example: 'Kowalski'),
                                    new OA\Property(
                                        property: 'roles',
                                        type: 'array',
                                        items: new OA\Items(type: 'string'),
                                        example: ['ROLE_USER']
                                    ),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'page', type: 'integer', example: 1),
                                new OA\Property(property: 'limit', type: 'integer', example: 10),
                                new OA\Property(property: 'total', type: 'integer', example: 100),
                                new OA\Property(property: 'pages', type: 'integer', example: 10),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not authenticated'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Brak uprawnień',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Access denied'),
                    ]
                )
            )
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $filters = array_filter([
            'email'     => $request->query->get('email'),
            'firstName' => $request->query->get('firstName'),
            'lastName'  => $request->query->get('lastName'),
        ], fn($v) => $v !== null && $v !== '');

        if ($request->query->has('approved')) {
            $filters['approved'] = filter_var($request->query->get('approved'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($filters['approved'] === null) { unset($filters['approved']); }
        }
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
                'approved'  => $u->isApproved(),
                'approvedAt'=> $u->getApprovedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return $this->json([
            'data' => $users,
            'meta' => [
                'page'  => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int)ceil($total / $limit),
            ]
        ]);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaj użytkownika',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email','password','firstName','lastName'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@fixourcity.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'P@ssw0rd!'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'Jan'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Kowalski'),
                    new OA\Property(
                        property: 'roles',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['ROLE_USER']
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Użytkownik utworzony',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@fixourcity.com'),
                        new OA\Property(property: 'firstName', type: 'string', example: 'Jan'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Kowalski'),
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['ROLE_USER']
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid input data'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not authenticated'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Brak uprawnień',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Access denied'),
                    ]
                )
            )
        ]
    )]
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
            'approved'  => $user->isApproved(),
            'approvedAt'=> $user->getApprovedAt(),
        ], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz użytkownika po ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dane użytkownika',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@fixourcity.com'),
                        new OA\Property(property: 'firstName', type: 'string', example: 'Jan'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Kowalski'),
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['ROLE_USER']
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Nie znaleziono',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not authenticated'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Brak uprawnień',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Access denied'),
                    ]
                )
            )
        ]
    )]
    public function show(User $user): JsonResponse
    {
        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName'  => $user->getLastName(),
            'roles'     => $user->getRoles(),
            'approved'  => $user->isApproved(),
            'approvedAt'=> $user->getApprovedAt(),
        ]);
    }

    #[Route('/{id}', methods: ['PUT','PATCH'])]
    #[OA\Patch(
        summary: 'Aktualizuj użytkownika',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'firstName', type: 'string'),
                    new OA\Property(property: 'lastName', type: 'string'),
                    new OA\Property(
                        property: 'roles',
                        type: 'array',
                        items: new OA\Items(type: 'string')
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zaktualizowano użytkownika',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@fixourcity.com'),
                        new OA\Property(property: 'firstName', type: 'string', example: 'Jan'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Kowalski'),
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['ROLE_USER']
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Nie znaleziono',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid input data'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not authenticated'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Brak uprawnień',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Access denied'),
                    ]
                )
            )
        ]
    )]
    public function update(User $user, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $actor = $this->getUser();
        $user = $this->userService->update($user, $payload, $actor);

        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName'  => $user->getLastName(),
            'roles'     => $user->getRoles(),
            'approved'  => $user->isApproved(),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń użytkownika',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Usunięto użytkownika'
            ),
            new OA\Response(
                response: 404,
                description: 'Nie znaleziono',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not authenticated'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Brak uprawnień',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Access denied'),
                    ]
                )
            )
        ]
    )]
    public function delete(User $user): JsonResponse
    {
        $this->userService->delete($user);
        return $this->json(null, 204);
    }
    
    #[Route('/{id}/approve', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Zatwierdź użytkownika',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Użytkownik zatwierdzony')]
    )]
    public function approve(User $user): JsonResponse
    {
        /** @var User $admin */
        $admin = $this->getUser();
        $user = $this->userService->approve($user, $admin);

        return $this->json([
            'id'         => $user->getId(),
            'approved'   => $user->isApproved(),
            'approvedAt' => $user->getApprovedAt()?->format(\DateTimeInterface::ATOM),
        ]);
    }

    #[Route('/{id}/unapprove', methods: ['PATCH'])]
    public function unapprove(User $user): JsonResponse
    {
        $user = $this->userService->unapprove($user);
        return $this->json([
            'id'         => $user->getId(),
            'approved'   => $user->isApproved(),
            'approvedAt' => $user->getApprovedAt()?->format(\DateTimeInterface::ATOM),
        ]);
    }
}
