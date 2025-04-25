<?php
namespace App\Entity;

use App\Repository\ReportFilterRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Report;

#[ORM\Entity(repositoryClass: ReportFilterRepository::class)]
class ReportFilter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $label;

    #[ORM\OneToMany(mappedBy: 'filter', targetEntity: Report::class)]
    private Collection $reports;

    public function __construct()
    {
        $this->reports = new ArrayCollection();
    }

    // Gettery i settery...
}
