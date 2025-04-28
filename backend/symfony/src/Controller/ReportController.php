<?php

namespace App\Controller;

use App\Service\ReportService;
use App\Repository\ReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reports')]
class ReportController extends AbstractController
{
    private ReportService $reportService;
    private ReportRepository $reportRepository;

    public function __construct(ReportService $reportService, ReportRepository $reportRepository)
    {
        $this->reportService = $reportService;
        $this->reportRepository = $reportRepository;
    }

    #[Route('', name: 'report_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || !isset($data['description'])) {
            return $this->json(['error' => 'Missing fields'], 400);
        }

        $report = $this->reportService->createReport($data['title'], $data['description']);

        return $this->json([
            'id' => $report->getId(),
            'title' => $report->getTitle(),
            'description' => $report->getDescription(),
            'createdAt' => $report->getCreatedAt(),
        ], 201);
    }

    #[Route('', name: 'report_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $reports = $this->reportRepository->findAll();

        $data = array_map(function($report) {
            return [
                'id' => $report->getId(),
                'title' => $report->getTitle(),
                'description' => $report->getDescription(),
                'createdAt' => $report->getCreatedAt(),
            ];
        }, $reports);

        return $this->json($data);
    }
}
