<?php

namespace App\Controller;

use App\Repository\ReportRepository;
use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Reports')]
#[Route(path: '/api/v1/reports')]
class ReportController extends AbstractController
{
    public function __construct(
        private ReportService    $reportService,
        private ReportRepository $reportRepo
    ) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz listę zgłoszeń z paginacją, filtrowaniem i sortowaniem',
        parameters: [
            new OA\Parameter(name: 'page',     in: 'query', schema: new OA\Schema(type: 'integer'), example: 1, description: 'Numer strony'),
            new OA\Parameter(name: 'limit',    in: 'query', schema: new OA\Schema(type: 'integer'), example: 10, description: 'Liczba elementów na stronę'),
            new OA\Parameter(name: 'status',   in: 'query', schema: new OA\Schema(type: 'string'),  example: 'Nowe', description: 'Filtr po statusie'),
            new OA\Parameter(name: 'category', in: 'query', schema: new OA\Schema(type: 'string'),  example: 'Oświetlenie', description: 'Filtr po kategorii'),
            new OA\Parameter(name: 'sort',     in: 'query', schema: new OA\Schema(type: 'string'),  example: 'createdAt', description: 'Pole sortowania'),
            new OA\Parameter(name: 'order',    in: 'query', schema: new OA\Schema(type: 'string', enum: ['ASC','DESC']), example: 'DESC', description: 'Kierunek sortowania'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca obiekt zawierający klucze data i meta',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'title', type: 'string'),
                                new OA\Property(property: 'description', type: 'string'),
                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'status', type: 'string'),
                                new OA\Property(property: 'category', type: 'string'),
                                new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string')),
                            ]
                        )),
                        new OA\Property(property: 'meta', type: 'object', properties: [
                            new OA\Property(property: 'total', type: 'integer'),
                            new OA\Property(property: 'page',  type: 'integer'),
                            new OA\Property(property: 'limit', type: 'integer'),
                        ]),
                    ]
                )
            )
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $page      = max(1, (int)$request->query->get('page', 1));
        $limit     = min(100, max(1, (int)$request->query->get('limit', 10)));
        $filters   = [
            'status'   => $request->query->get('status'),
            'category' => $request->query->get('category'),
        ];
        $sortField = $request->query->get('sort', 'createdAt');
        $sortOrder = strtoupper($request->query->get('order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $items = $this->reportRepo->findFiltered($filters, $page, $limit, $sortField, $sortOrder);
        $total = $this->reportRepo->countFiltered($filters);

        $data = array_map(fn($r) => $this->reportService->serialize($r), $items);

        return $this->json([
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page'  => $page,
                'limit' => $limit,
            ],
        ], 200);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Utwórz nowe zgłoszenie',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'description'],
                properties: [
                    new OA\Property(property: 'title',       type: 'string', example: 'Awaria latarni'),
                    new OA\Property(property: 'description', type: 'string', example: 'Latarnia przy ul. X nie świeci.'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Zgłoszenie utworzone'),
            new OA\Response(response: 400, description: 'Brak wymaganych pól')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (empty($payload['title']) || empty($payload['description'])) {
            return $this->json(['error' => 'Missing fields'], 400);
        }

        $report = $this->reportService->createReport(
            $payload['title'],
            $payload['description']
        );

        return $this->json($this->reportService->serialize($report), 201);
    }

    #[Route(path: '/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz szczegóły zgłoszenia',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca szczegóły zgłoszenia',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id',          type: 'integer'),
                        new OA\Property(property: 'title',       type: 'string'),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'createdAt',   type: 'string', format: 'date-time'),
                        new OA\Property(property: 'status',      type: 'string'),
                        new OA\Property(property: 'category',    type: 'string'),
                        new OA\Property(property: 'tags',        type: 'array', items: new OA\Items(type: 'string')),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Zgłoszenie nie znalezione')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $report = $this->reportRepo->find($id);
        if (!$report) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json($this->reportService->serialize($report), 200);
    }

    #[Route(path: '/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Aktualizuj zgłoszenie',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title',       type: 'string', example: 'Nowy tytuł'),
                    new OA\Property(property: 'description', type: 'string', example: 'Zaktualizowany opis'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zgłoszenie zaktualizowane',
                content: new OA\JsonContent(type: 'object')
            ),
            new OA\Response(response: 404, description: 'Zgłoszenie nie znalezione'),
            new OA\Response(response: 400, description: 'Brak danych do aktualizacji')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $report = $this->reportRepo->find($id);
        if (!$report) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return $this->json(['error' => 'No data provided'], 400);
        }

        $updated = $this->reportService->updateReport($report, $data);
        return $this->json($this->reportService->serialize($updated), 200);
    }

    #[Route(path: '/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń zgłoszenie',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zgłoszenie usunięte'),
            new OA\Response(response: 404, description: 'Zgłoszenie nie znalezione')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $report = $this->reportRepo->find($id);
        if (!$report) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $this->reportService->deleteReport($report);
        return $this->json(null, 204);
    }
}
