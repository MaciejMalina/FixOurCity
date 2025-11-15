<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CanReportVoter extends Voter
{
    public const CAN_REPORT = 'CAN_REPORT';

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::CAN_REPORT;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) return false;
        return $user->isApproved();
    }
}
