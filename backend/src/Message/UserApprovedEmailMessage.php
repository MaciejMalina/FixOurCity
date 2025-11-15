<?php

namespace App\Message;

final class UserApprovedEmailMessage
{
    public function __construct(
        private string $email,
        private string $firstName
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }
}
