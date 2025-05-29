<?php

namespace App\Entity;

use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: "audit_logs")]
#[ORM\Index(columns: ["created_at"], name: "audit_created_idx")]
#[ORM\Index(columns: ["target_type", "target_id"], name: "audit_target_idx")]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $action;

    #[ORM\Column(length: 50)]
    private string $targetType;

    #[ORM\Column]
    private int $targetId;

    #[ORM\Column(name: "created_at", type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "auditLogs")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): string
    {
        return $this->action;
    }
    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getTargetType(): string
    {
        return $this->targetType;
    }
    public function setTargetType(string $targetType): self
    {
        $this->targetType = $targetType;
        return $this;
    }

    public function getTargetId(): int
    {
        return $this->targetId;
    }
    public function setTargetId(int $targetId): self
    {
        $this->targetId = $targetId;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
