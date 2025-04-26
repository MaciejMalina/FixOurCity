<?php

namespace App\EventListener;

use App\Repository\BlacklistedTokenRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;

class CheckBlacklistedTokenListener
{
    private BlacklistedTokenRepository $blacklistedTokenRepository;

    public function __construct(BlacklistedTokenRepository $blacklistedTokenRepository)
    {
        $this->blacklistedTokenRepository = $blacklistedTokenRepository;
    }

    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        $token = $event->getToken();
        $jwt = $event->getPayload();

        $rawToken = $event->getToken();

        if ($this->blacklistedTokenRepository->findOneBy(['token' => $rawToken])) {
            $event->markAsInvalid();
        }
    }
}
