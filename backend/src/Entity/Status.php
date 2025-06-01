<?php
namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
#[ORM\Table(name: 'status')]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $label;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Report::class)]
    private Collection $reports;

    public function __construct()
    {
        $this->reports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getLabel(): string
    {
        return $this->label;
    }
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getReports(): Collection
    {
        return $this->reports;
    }
}
