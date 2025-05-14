<?php

namespace App\Service;

use App\Entity\Report;
use App\Repository\StatusRepository;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReportService
{
    public function __construct(
        private EntityManagerInterface $em,
        private StatusRepository       $statusRepo,
        private CategoryRepository     $categoryRepo,
        private TagRepository          $tagRepo
    ) {}

    public function createReport(string $title, string $description): Report
    {
        $report = new Report();
        $report
            ->setTitle($title)
            ->setDescription($description);
        $this->em->persist($report);
        $this->em->flush();

        return $report;
    }

    /**
     * Aktualizuje dowolne pola zgłoszenia: title, description,
     * status (po ID), category (po ID) oraz listę tagów (tablica ID).
     */
    public function updateReport(Report $report, array $data): Report
    {
        if (isset($data['title'])) {
            $report->setTitle($data['title']);
        }

        if (isset($data['description'])) {
            $report->setDescription($data['description']);
        }

        // Zmiana statusu po przekazanym statusId
        if (isset($data['statusId'])) {
            $status = $this->statusRepo->find($data['statusId']);
            if ($status) {
                $report->setStatus($status);
            }
        }

        // Zmiana kategorii po przekazanym categoryId
        if (isset($data['categoryId'])) {
            $category = $this->categoryRepo->find($data['categoryId']);
            if ($category) {
                $report->setCategory($category);
            }
        }

        // Zmiana tagów — oczekujemy tablicy tagId w $data['tags']
        if (isset($data['tags']) && is_array($data['tags'])) {
            // Usuń wszystkie poprzednie powiązania
            foreach ($report->getTags() as $oldTag) {
                $report->removeTag($oldTag);
            }
            // Dodaj nowe
            foreach ($data['tags'] as $tagId) {
                $tag = $this->tagRepo->find($tagId);
                if ($tag) {
                    $report->addTag($tag);
                }
            }
        }

        $this->em->flush();
        return $report;
    }

    public function deleteReport(Report $report): void
    {
        $this->em->remove($report);
        $this->em->flush();
    }

    public function serialize(Report $r): array
    {
        return [
            'id'          => $r->getId(),
            'title'       => $r->getTitle(),
            'description' => $r->getDescription(),
            'createdAt'   => $r->getCreatedAt()->format('c'),
            'status'      => $r->getStatus()?->getLabel(),
            'category'    => $r->getCategory()?->getName(),
            'tags'        => array_map(
                fn($t) => $t->getName(),
                $r->getTags()->toArray()
            ),
        ];
    }
}
