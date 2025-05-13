<?php

namespace App\Service;

use App\Entity\Report;
use Doctrine\ORM\EntityManagerInterface;

class ReportService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function createReport(string $title, string $description): Report
    {
        $report = new Report();
        $report->setTitle($title);
        $report->setDescription($description);
        $this->em->persist($report);
        $this->em->flush();
        return $report;
    }

    public function updateReport(Report $report, array $data): Report
    {
        if (isset($data['title'])) $report->setTitle($data['title']);
        if (isset($data['description'])) $report->setDescription($data['description']);
        $this->em->flush();
        return $report;
    }

    public function deleteReport(Report $report): void
    {
        $this->em->remove($report);
        $this->em->flush();
    }
}
