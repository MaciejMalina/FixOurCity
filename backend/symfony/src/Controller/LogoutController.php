<?php

namespace App\Controller;

use App\Entity\RefreshToken;
use App\Entity\BlacklistedToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA; // <-- Dodajemy!
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

#[Route('/api')]
class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    #[OA\Post(
        path: '/api/logout',
        summary: 'Wylogowanie użytkownika',
        description: 'Wylogowuje użytkownika poprzez dodanie tokenu do czarnej listy.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'Authorization', type: 'string', example: 'Bearer {token}')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wylogowano pomyślnie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Successfully logged out')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Brak przesłanego tokenu',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'No token provided')
                    ]
                )
            )
        ]
    )]
    public function logout(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $token = $request->headers->get('Authorization');

        if (!$token) {
            return $this->json(['error' => 'No token provided'], 400);
        }

        $token = str_replace('Bearer ', '', $token);

        $blacklistedToken = new BlacklistedToken($token);
        $entityManager->persist($blacklistedToken);
        $entityManager->flush();

        return $this->json(['message' => 'Successfully logged out']);
    }
}
