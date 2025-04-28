<?php

namespace App\EventListener;

use App\Repository\BlacklistedTokenRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class CheckBlacklistedTokenListener
{
    private BlacklistedTokenRepository $blacklistedTokenRepository;
    private RequestStack $requestStack;

    public function __construct(
        BlacklistedTokenRepository $blacklistedTokenRepository,
        RequestStack $requestStack
    ) {
        $this->blacklistedTokenRepository = $blacklistedTokenRepository;
        $this->requestStack = $requestStack;
    }

    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return;
        }

        $token = str_replace('Bearer ', '', $authHeader);

        $blacklisted = $this->blacklistedTokenRepository->findOneBy([
            'token' => $token,
        ]);

        if ($blacklisted) {
            $event->markAsInvalid();
        }
    }
}
