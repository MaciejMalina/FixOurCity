<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\Index(name: "email_idx", columns: ["email"])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(length: 100)]
    private string $firstName;

    #[ORM\Column(length: 100)]
    private string $lastName;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Report::class, orphanRemoval: true)]
    private Collection $reports;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: AuditLog::class)]
    private Collection $auditLogs;

    #[ORM\ManyToMany(targetEntity: Report::class, inversedBy: "followers")]
    #[ORM\JoinTable(name: "report_follower")]
    private Collection $followedReports;

    public function __construct()
    {
        $this->reports = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->auditLogs = new ArrayCollection();
        $this->followedReports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void {}


    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setUser($this);
        }
        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->removeElement($report)) {
            if ($report->getUser() === $this) {
                $report->setUser(null);
            }
        }
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getAuditLogs(): Collection
    {
        return $this->auditLogs;
    }

    public function getFollowedReports(): Collection
    {
        return $this->followedReports;
    }

    public function addFollowedReport(Report $report): self
    {
        if (!$this->followedReports->contains($report)) {
            $this->followedReports->add($report);
        }
        return $this;
    }

    public function removeFollowedReport(Report $report): self
    {
        $this->followedReports->removeElement($report);
        return $this;
    }
}
