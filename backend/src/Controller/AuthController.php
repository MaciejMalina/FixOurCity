<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
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
            new OA\Response(response: 201, description: 'Użytkownik zarejestrowany'),
            new OA\Response(response: 400, description: 'Błędne dane wejściowe')
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
            new OA\Response(response: 200, description: 'Zalogowano, ustawiono ciasteczka'),
            new OA\Response(response: 401, description: 'Nieprawidłowe dane logowania')
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
            new OA\Response(response: 200, description: 'Nowy access token, odświeżono ciasteczka'),
            new OA\Response(response: 401, description: 'Brak lub nieważny refresh token')
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
            new OA\Response(response: 204, description: 'Wylogowano pomyślnie'),
            new OA\Response(response: 401, description: 'Brak ważnego refresh tokena')
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $user = $this->getUser();
        return $this->authService->logout($request, $user);
    }
}
