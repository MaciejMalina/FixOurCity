<?php

namespace App\Controller;

use App\Entity\Report;
use App\Repository\ReportRepository;
use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Reports')]
#[Route('/api/reports')]
class ReportController extends AbstractController
{
    public function __construct(
        private ReportService $reportService,
        private ReportRepository $reportRepository
    ) {}

    #[Route('', name: 'report_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista zgłoszeń z paginacją i filtrowaniem',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'Nowe'),
            new OA\Parameter(name: 'category', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'Oświetlenie'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca listę zgłoszeń',
                content: new OA\JsonContent(type: 'array', items: new OA\Items())
            )
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query->get('status'),
            'category' => $request->query->get('category'),
        ];
        $page = max(1, (int)$request->query->get('page', 1));
        $reports = $this->reportRepository->findFiltered($filters, $page);

        return $this->json(array_map(fn($r) => $this->serialize($r), $reports));
    }

    #[Route('', name: 'report_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Utwórz zgłoszenie',
        tags: ['Reports'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'description'],
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Zgłoszenie utworzone'),
            new OA\Response(response: 400, description: 'Błędne dane')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'], $data['description'])) {
            return $this->json(['error' => 'Missing fields'], 400);
        }

        $report = $this->reportService->createReport($data['title'], $data['description']);
        return $this->json($this->serialize($report), 201);
    }

    #[Route('/{id}', name: 'report_show', methods: ['GET'])]
    #[OA\Get(
        summary: 'Szczegóły zgłoszenia',
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Zgłoszenie znalezione'),
            new OA\Response(response: 404, description: 'Nie znaleziono zgłoszenia')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $report = $this->reportRepository->find($id);
        return $report
            ? $this->json($this->serialize($report))
            : $this->json(['error' => 'Not found'], 404);
    }

    #[Route('/{id}', name: 'report_update', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Aktualizuj zgłoszenie',
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Zgłoszenie zaktualizowane'),
            new OA\Response(response: 404, description: 'Nie znaleziono zgłoszenia')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $report = $this->reportRepository->find($id);
        if (!$report) return $this->json(['error' => 'Not found'], 404);

        $updated = $this->reportService->updateReport($report, $data);
        return $this->json($this->serialize($updated));
    }

    #[Route('/{id}', name: 'report_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń zgłoszenie',
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zgłoszenie usunięte'),
            new OA\Response(response: 404, description: 'Nie znaleziono zgłoszenia')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $report = $this->reportRepository->find($id);
        if (!$report) return $this->json(['error' => 'Not found'], 404);

        $this->reportService->deleteReport($report);
        return $this->json(null, 204);
    }

    private function serialize(Report $r): array
    {
        return [
            'id' => $r->getId(),
            'title' => $r->getTitle(),
            'description' => $r->getDescription(),
            'createdAt' => $r->getCreatedAt()->format('c'),
        ];
    }
}
