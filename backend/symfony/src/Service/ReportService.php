<?php

namespace App\Service;

use App\Entity\Report;
use Doctrine\ORM\EntityManagerInterface;

class ReportService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createReport(string $title, string $description): Report
    {
        $report = new Report();
        $report->setTitle($title);
        $report->setDescription($description);

        $this->em->persist($report);
        $this->em->flush();

        return $report;
    }
}
