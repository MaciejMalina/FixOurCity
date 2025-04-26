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

    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $payload = $event->getPayload();

        if (!isset($payload['token'])) {
            return;
        }

        $token = $payload['token'];

        $blacklisted = $this->blacklistedTokenRepository->findOneBy([
            'token' => $token,
        ]);

        if ($blacklisted) {
            $event->markAsInvalid();
        }
    }
}
