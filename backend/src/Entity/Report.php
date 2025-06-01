<?php
namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ORM\Table(name: 'report')]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $longitude = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\ManyToOne(targetEntity: Status::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false)]
    private Status $status;

    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Image::class, cascade: ['persist','remove'])]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Comment::class, cascade: ['persist','remove'])]
    private Collection $comments;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->images    = new ArrayCollection();
        $this->comments  = new ArrayCollection();
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
    public function setDescription(string $desc): self
    {
        $this->description = $desc;
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

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }
    public function setLatitude(?string $lat): self
    {
        $this->latitude = $lat;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }
    public function setLongitude(?string $lng): self
    {
        $this->longitude = $lng;
        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
    public function setCategory(Category $cat): self
    {
        $this->category = $cat;
        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
    public function setStatus(Status $st): self
    {
        $this->status = $st;
        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }
    public function addImage(Image $img): self
    {
        if (!$this->images->contains($img)) {
            $this->images->add($img);
            $img->setReport($this);
        }
        return $this;
    }
    public function removeImage(Image $img): self
    {
        if ($this->images->removeElement($img)) {
            if ($img->getReport() === $this) {
                $img->setReport(null);
            }
        }
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }
    public function addComment(Comment $c): self
    {
        if (!$this->comments->contains($c)) {
            $this->comments->add($c);
            $c->setReport($this);
        }
        return $this;
    }
    public function removeComment(Comment $c): self
    {
        if ($this->comments->removeElement($c)) {
            if ($c->getReport() === $this) {
                $c->setReport(null);
            }
        }
        return $this;
    }
}
