<?php
namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\Table(name: 'image')]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private string $url;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private Report $report;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUrl(): string
    {
        return $this->url;
    }
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeInterface $dt): self
    {
        $this->createdAt = $dt;
        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }
    public function setReport(Report $r): self
    {
        $this->report = $r;
        return $this;
    }
}
