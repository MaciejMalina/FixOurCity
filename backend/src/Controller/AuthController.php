<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Auth')]
#[Route(path: '/api')]
class AuthController extends AbstractController
{
    public function __construct(private AuthService $authService) {}

    #[Route(path: '/register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/register',
        summary: 'Rejestracja nowego użytkownika',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'firstName', 'lastName'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'P@ssw0rd!'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'Jan'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Kowalski'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Użytkownik zarejestrowany'),
            new OA\Response(response: 400, description: 'Błędne dane wejściowe')
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $user = $this->authService->register(json_decode($request->getContent(), true));
        return $this->json([
            'id'    => $user->getId(),
            'email' => $user->getEmail()
        ], 201);
    }

    #[Route(path: '/login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login',
        summary: 'Logowanie użytkownika (zwraca access token + refresh token jako HttpOnly cookies)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'P@ssw0rd!')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zalogowano – ustawiono ACCESS_TOKEN i REFRESH_TOKEN w HttpOnly cookies',
                headers: [
                    new OA\Header(header: 'Set-Cookie', description: 'ACCESS_TOKEN i REFRESH_TOKEN'),
                ]
            ),
            new OA\Response(response: 401, description: 'Nieprawidłowe dane logowania')
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        return $this->authService->login(json_decode($request->getContent(), true));
    }

    #[Route(path: '/token/refresh', methods: ['POST'])]
    #[OA\Post(
        path: '/api/token/refresh',
        summary: 'Odświeżenie access tokena przy pomocy refresh tokena',
        description: 'Refresh token pobierany jest z HttpOnly cookie REFRESH_TOKEN',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wydano nowy access token (cookie ACCESS_TOKEN również odświeżone)',
                headers: [
                    new OA\Header(header: 'Set-Cookie', description: 'Nowe ACCESS_TOKEN i REFRESH_TOKEN')
                ]
            ),
            new OA\Response(response: 401, description: 'Brak lub nieważny refresh token')
        ]
    )]
    public function refresh(Request $request): JsonResponse
    {
        return $this->authService->refresh($request);
    }

    #[Route(path: '/logout', methods: ['POST'])]
    #[OA\Post(
        path: '/api/logout',
        summary: 'Wylogowanie użytkownika',
        description: 'Blacklistuje refresh token i usuwa oba cookies (ACCESS_TOKEN, REFRESH_TOKEN)',
        responses: [
            new OA\Response(response: 204, description: 'Wylogowano pomyślnie'),
            new OA\Response(response: 401, description: 'Brak ważnego refresh tokena')
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        return $this->authService->logout($request);
    }
}
