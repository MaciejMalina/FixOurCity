<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class BlacklistedToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text', unique: true)]
    private string $token;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $blacklistedAt;

    public function __construct(string $token)
    {
        $this->token = $token;
        $this->blacklistedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getBlacklistedAt(): \DateTimeInterface
    {
        return $this->blacklistedAt;
    }
}
