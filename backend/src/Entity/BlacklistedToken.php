<?php

namespace App\Entity;

use App\Repository\BlacklistedTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlacklistedTokenRepository::class)]
#[ORM\Table(name: "blacklisted_tokens")]
#[ORM\Index(columns: ["token"], name: "token_idx")]
#[ORM\Index(columns: ["expired_at"], name: "expired_idx")]
class BlacklistedToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 512, unique: true)]
    private string $token;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiredAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getToken(): string { return $this->token; }
    public function setToken(string $token): self { $this->token = $token; return $this; }

    public function getExpiredAt(): \DateTimeImmutable { return $this->expiredAt; }
    public function setExpiredAt(\DateTimeImmutable $expiredAt): self { $this->expiredAt = $expiredAt; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
}
