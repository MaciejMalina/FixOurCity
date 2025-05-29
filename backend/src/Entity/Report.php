<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ORM\Index(name: "created_at_idx", columns: ["created_at"])]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column]
    private float $lat;

    #[ORM\Column]
    private float $lng;

    #[ORM\Column(name: "created_at", type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: "reports")]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Status $status = null;

    #[ORM\ManyToOne]
    private ?Category $category = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: "reports")]
    #[ORM\JoinTable(name: "report_tag")]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: "report", targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: "report", targetEntity: Image::class, cascade: ['persist', 'remove'])]
    private Collection $images;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "followedReports")]
    private Collection $followers;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->followers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLat(): float
    {
        return $this->lat;
    }
    public function setLat(float $lat): self
    {
        $this->lat = $lat;
        return $this;
    }

    public function getLng(): float
    {
        return $this->lng;
    }
    public function setLng(float $lng): self
    {
        $this->lng = $lng;
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

    public function getStatus(): ?Status
    {
        return $this->status;
    }
    public function setStatus(?Status $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }
    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }
    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }
    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }
    public function getImages(): Collection
    {
        return $this->images;
    }
    public function getFollowers(): Collection
    {
        return $this->followers;
    }
}
