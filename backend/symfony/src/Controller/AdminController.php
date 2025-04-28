<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/stats', name: 'admin_stats', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/stats',
        summary: 'Pobierz statystyki administratora',
        description: 'Zwraca informacje dostępne tylko dla użytkownika z rolą ADMIN.',
        tags: ['Admin'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statystyki zwrócone poprawnie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'You are viewing admin statistics!'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function stats(): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'message' => 'You are viewing admin statistics!',
        ]);
    }
}
