<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Auth')]
#[Route('/api/v1/auth')]
class AuthController extends AbstractController
{
    public function __construct(private AuthService $authService) {}

    #[Route('/register', methods: ['POST'])]
    #[OA\Post(
        summary: 'Rejestracja nowego użytkownika',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email','password','firstName','lastName'],
                properties: [
                    new OA\Property(property: 'email',      type: 'string', format: 'email',    example: 'user@example.com'),
                    new OA\Property(property: 'password',   type: 'string', format: 'password', example: 'P@ssw0rd!'),
                    new OA\Property(property: 'firstName',  type: 'string',                    example: 'Jan'),
                    new OA\Property(property: 'lastName',   type: 'string',                    example: 'Kowalski'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Użytkownik zarejestrowany',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id',    type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string',  format: 'email', example: 'user@example.com'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid input data'),
                    ]
                )
            )
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $user = $this->authService->register(json_decode($request->getContent(), true));
        return $this->json([
            'id'    => $user->getId(),
            'email' => $user->getEmail(),
        ], 201);
    }

    #[Route('/login', methods: ['POST'])]
    #[OA\Post(
        summary: 'Logowanie użytkownika (zwraca tokeny jako HttpOnly cookies)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email','password'],
                properties: [
                    new OA\Property(property: 'email',    type: 'string', format: 'email',    example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'P@ssw0rd!')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zalogowano, ustawiono ciasteczka',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Nieprawidłowe dane logowania',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid credentials')
                    ]
                )
            )
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        return $this->authService->login(json_decode($request->getContent(), true));
    }

    #[Route('/token/refresh', methods: ['POST'])]
    #[OA\Post(
        summary: 'Odświeżenie access tokena przy pomocy refresh tokena',
        description: 'Refresh token czytany z HttpOnly cookie',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Nowy access token, odświeżono ciasteczka',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak lub nieważny refresh token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid or expired refresh token')
                    ]
                )
            )
        ]
    )]
    public function refresh(Request $request): JsonResponse
    {
        return $this->authService->refresh($request);
    }

    #[Route('/logout', methods: ['POST'])]
    #[OA\Post(
        summary: 'Wylogowanie użytkownika',
        description: 'Blacklistuje tokeny i usuwa ciasteczka',
        responses: [
            new OA\Response(
                response: 204,
                description: 'Wylogowano pomyślnie'
            ),
            new OA\Response(
                response: 401,
                description: 'Brak ważnego refresh tokena',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Brak ważnego refresh tokena')
                    ]
                )
            )
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $user = $this->getUser();
        return $this->authService->logout($request, $user);
    }

    #[Route('/me', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz dane aktualnie zalogowanego użytkownika',
        description: 'Zwraca email, imię, nazwisko i role aktualnie zalogowanego użytkownika na podstawie JWT.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dane użytkownika',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id',        type: 'integer', example: 1),
                        new OA\Property(property: 'email',     type: 'string',  example: 'admin@fixourcity.com'),
                        new OA\Property(property: 'firstName', type: 'string',  example: 'Jan'),
                        new OA\Property(property: 'lastName',  type: 'string',  example: 'Kowalski'),
                        new OA\Property(property: 'roles',     type: 'array',   items: new OA\Items(type: 'string'), example: ['ROLE_ADMIN'])
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not authenticated')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Brak uprawnień (Access Denied)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Access Denied')
                    ]
                )
            )
        ]
    )]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }
        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName'  => $user->getLastName(),
            'roles'     => $user->getRoles(),
        ]);
    }
}
