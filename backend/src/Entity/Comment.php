<?php
namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'comment')]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'string', length: 100)]
    private string $author;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'comments')]
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
    public function getContent(): string
    {
        return $this->content;
    }
    public function setContent(string $c): self
    {
        $this->content = $c;
        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }
    public function setAuthor(string $a): self
    {
        $this->author = $a;
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
