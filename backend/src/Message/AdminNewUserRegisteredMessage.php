<?php

namespace App\Message;

final class AdminNewUserRegisteredMessage
{
    public function __construct(
        private int $userId,
        private string $email,
        private string $firstName,
        private string $lastName
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }
}
