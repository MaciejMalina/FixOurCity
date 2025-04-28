<?php

namespace App\Controller;

use App\Service\ReportService;
use App\Repository\ReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

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
    #[OA\Post(
        path: '/api/reports',
        summary: 'Utwórz nowe zgłoszenie',
        description: 'Tworzy nowe zgłoszenie z tytułem i opisem.',
        tags: ['Reports'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Uszkodzona latarnia'),
                    new OA\Property(property: 'description', type: 'string', example: 'Latarnia na rogu ulicy nie świeci od kilku dni.')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Zgłoszenie utworzone pomyślnie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'title', type: 'string'),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Brak wymaganych pól')
        ]
    )]
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
    #[OA\Get(
        path: '/api/reports',
        summary: 'Pobierz listę zgłoszeń',
        description: 'Zwraca listę wszystkich zgłoszeń.',
        tags: ['Reports'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista zgłoszeń',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'title', type: 'string'),
                            new OA\Property(property: 'description', type: 'string'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time')
                        ]
                    )
                )
            )
        ]
    )]
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
