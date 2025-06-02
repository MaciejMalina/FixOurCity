<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\RefreshToken;
use App\Entity\BlacklistedToken;
use App\Message\SendWelcomeEmailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Repository\RefreshTokenRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenRepository $rtRepo,
        private MessageBusInterface $bus
    ) {}

    public function register(array $data): User
    {
        $user = new User();
        $user->setEmail($data['email'])
             ->setFirstName($data['firstName'])
             ->setLastName($data['lastName'])
             ->setRoles(['ROLE_USER'])
             ->setPassword($this->hasher->hashPassword($user, $data['password']));

        $this->em->persist($user);
        $this->em->flush();
        $this->bus->dispatch(new SendWelcomeEmailMessage($user->getEmail()));
        return $user;
    }

    public function login(array $credentials): JsonResponse
    {
        $user = $this->em->getRepository(User::class)
                        ->findOneBy(['email' => $credentials['email']]);
        if (!$user || !$this->hasher->isPasswordValid($user, $credentials['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        $accessToken = $this->jwtManager->create($user);
        $accessCookie = Cookie::create('ACCESS_TOKEN')
            ->withValue($accessToken)
            ->withHttpOnly(true)
            ->withSecure(false)
            ->withSameSite('lax')
            ->withPath('/')
            ->withExpires((new \DateTimeImmutable())->modify('+1 hour'));

        $refreshTokenValue = bin2hex(random_bytes(32));
        $refreshExpires     = new \DateTimeImmutable('+7 days');
        $refreshToken       = new RefreshToken($user, $refreshTokenValue, $refreshExpires);
        $this->em->persist($refreshToken);
        $this->em->flush();

        $refreshCookie = Cookie::create('REFRESH_TOKEN')
            ->withValue($refreshTokenValue)
            ->withHttpOnly(true)
            ->withSecure(false)
            ->withSameSite('lax')
            ->withPath('/')
            ->withExpires($refreshExpires);

        $resp = new JsonResponse(['token' => $accessToken], 200);
        $resp->headers->setCookie($accessCookie);
        $resp->headers->setCookie($refreshCookie);

        return $resp;
    }

    public function refresh(Request $request): JsonResponse
    {
        $refreshValue = $request->cookies->get('REFRESH_TOKEN');
        $rt = $this->rtRepo->findValid($refreshValue);
        if (!$rt) {
            return new JsonResponse(['error' => 'Invalid or expired refresh token'], 401);
        }

        $this->rtRepo->revoke($rt);

        $user = $rt->getUser();
        $accessToken = $this->jwtManager->create($user);
        $accessCookie = Cookie::create('ACCESS_TOKEN')
            ->withValue($accessToken)
            ->withHttpOnly(true)
            ->withSecure(true)
            ->withSameSite('lax')
            ->withPath('/')
            ->withExpires((new \DateTimeImmutable())->modify('+1 hour'));

        $newValue = bin2hex(random_bytes(32));
        $newExpires = new \DateTimeImmutable('+7 days');
        $newRt = new RefreshToken($user, $newValue, $newExpires);
        $this->em->persist($newRt);
        $this->em->flush();

        $refreshCookie = Cookie::create('REFRESH_TOKEN')
            ->withValue($newValue)
            ->withHttpOnly(true)
            ->withSecure(true)
            ->withSameSite('lax')
            ->withPath('/')
            ->withExpires($newExpires);

        $resp = new JsonResponse(['token' => $accessToken], 200);
        return $resp
            ->headers->setCookie($accessCookie)
            ->headers->setCookie($refreshCookie);
    }

    public function logout(Request $request, ?User $user = null): JsonResponse
    {
        $refreshValue = $request->cookies->get('REFRESH_TOKEN');
        if ($rt = $this->rtRepo->findValid($refreshValue)) {
            $this->rtRepo->revoke($rt);
        }

        $accessValue = $request->cookies->get('ACCESS_TOKEN')
            ?: str_replace('Bearer ', '', $request->headers->get('Authorization', ''));

        if ($accessValue) {
            $black = new BlacklistedToken();
            $black
                ->setToken($accessValue)
                ->setUser($user);
            $this->em->persist($black);
        }

        $this->em->flush();

        $removeAccess  = Cookie::create('ACCESS_TOKEN')
            ->withValue('')
            ->withExpires(new \DateTimeImmutable('-1 hour'))
            ->withPath('/');
        $removeRefresh = Cookie::create('REFRESH_TOKEN')
            ->withValue('')
            ->withExpires(new \DateTimeImmutable('-1 hour'))
            ->withPath('/');

        $resp = new JsonResponse(null, 204);
        return $resp
            ->headers->setCookie($removeAccess)
            ->headers->setCookie($removeRefresh);
    }
}
