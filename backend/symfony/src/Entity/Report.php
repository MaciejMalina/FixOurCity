<?php
namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Comment::class, cascade: ['remove'])]
    private Collection $comments;

    #[ORM\ManyToOne(targetEntity: ReportFilter::class, inversedBy: 'reports')]
    private ?ReportFilter $filter = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    // Gettery i settery...
}
