<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;

#[Route('/api/users')]
class ProfileController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/profile', name: 'user_profile', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/profile',
        summary: 'Pobierz profil aktualnie zalogowanego użytkownika',
        description: 'Zwraca email, role, imię i nazwisko użytkownika na podstawie tokenu JWT.',
        tags: ['Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dane użytkownika',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'email', type: 'string', example: 'example@domain.com'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function profile(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->json([
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName()
        ]);
    }

    #[Route('/change-password', name: 'user_change_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users/change-password',
        summary: 'Zmiana hasła zalogowanego użytkownika',
        description: 'Zmienia hasło użytkownika. Wymaga podania aktualnego oraz nowego hasła.',
        tags: ['Profile'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'currentPassword', type: 'string', example: 'oldpassword'),
                    new OA\Property(property: 'newPassword', type: 'string', example: 'newpassword')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Hasło zmienione pomyślnie'),
            new OA\Response(response: 400, description: 'Błąd walidacji (brak danych lub nieprawidłowe hasło)'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function changePassword(Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (empty($data['currentPassword']) || empty($data['newPassword'])) {
            return $this->json(['error' => 'Current and new passwords are required'], 400);
        }

        if ($user->getPassword() !== $data['currentPassword']) {
            return $this->json(['error' => 'Current password is incorrect'], 400);
        }

        $user->setPassword($data['newPassword']);
        $em->flush();

        return $this->json(['message' => 'Password changed successfully']);
    }
}
