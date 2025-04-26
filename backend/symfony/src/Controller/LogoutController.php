<?php

namespace App\Controller;

use App\Entity\RefreshToken;
use App\Entity\BlacklistedToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

#[Route('/api')]
class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request, RefreshTokenManagerInterface $refreshTokenManager, EntityManagerInterface $entityManager): JsonResponse
    {
        $token = $request->headers->get('Authorization');

        if (!$token) {
            return $this->json(['error' => 'No token provided'], 400);
        }

        $token = str_replace('Bearer ', '', $token);

        $user = $this->getUser();
        if ($user) {
            $refreshTokens = $entityManager->getRepository(RefreshToken::class)->findBy(['username' => $user->getEmail()]);

            foreach ($refreshTokens as $refreshToken) {
                $entityManager->remove($refreshToken);
            }
            $blacklistedToken = new BlacklistedToken($token);
            $entityManager->persist($blacklistedToken);
            $entityManager->flush();
        }

        return $this->json(['message' => 'Successfully logged out']);
    }
}
