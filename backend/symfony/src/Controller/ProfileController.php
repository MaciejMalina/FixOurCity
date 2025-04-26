<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users')]
class ProfileController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/profile', name: 'user_profile', methods: ['GET'])]
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
