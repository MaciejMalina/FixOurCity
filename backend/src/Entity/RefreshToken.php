<?php

namespace App\Entity;

use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name:"refresh_tokens")]
class RefreshToken
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length:64, unique:true)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable:false)]
    private User $user;

    #[ORM\Column(type:"datetime_immutable")]
    private \DateTimeImmutable $expiresAt;

    public function __construct(User $user, string $token, \DateTimeImmutable $expiresAt)
    {
        $this->user      = $user;
        $this->token     = $token;
        $this->expiresAt = $expiresAt;
    }

    public function getToken(): string
    {
        return $this->token;
    }
    public function getUser(): User
    {
        return $this->user;
    }
    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
