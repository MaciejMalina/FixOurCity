<?php
namespace App\Controller;

use App\Service\ReportService;
use App\Repository\ReportRepository;
use App\Security\CanReportVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Reports')]
#[Route(path: '/api/v1/reports')]
#[IsGranted('ROLE_USER')]
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
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Numer strony',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Liczba elementów na stronę',
                schema: new OA\Schema(type: 'integer'),
                example: 10
            ),
            new OA\Parameter(
                name: 'category',
                in: 'query',
                description: 'Filtruj po ID kategorii',
                schema: new OA\Schema(type: 'integer'),
                example: 2
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'Filtruj po ID statusu',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
            new OA\Parameter(
                name: 'title',
                in: 'query',
                description: 'Filtruj po fragmencie tytułu (ILIKE)',
                schema: new OA\Schema(type: 'string'),
                example: 'Awaria'
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                description: 'Pole sortowania (allowed: createdAt, title)',
                schema: new OA\Schema(type: 'string'),
                example: 'createdAt'
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                description: 'Kierunek sortowania (ASC lub DESC)',
                schema: new OA\Schema(type: 'string', enum: ['ASC','DESC']),
                example: 'DESC'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca obiekt z kluczami data i meta',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',          type: 'integer'),
                                    new OA\Property(property: 'title',       type: 'string'),
                                    new OA\Property(property: 'description', type: 'string'),
                                    new OA\Property(property: 'createdAt',   type: 'string', format: 'date-time'),
                                    new OA\Property(
                                        property: 'latitude',
                                        type: 'string',
                                        example: '50.06143'
                                    ),
                                    new OA\Property(
                                        property: 'longitude',
                                        type: 'string',
                                        example: '19.93658'
                                    ),
                                    new OA\Property(
                                        property: 'category',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id',   type: 'integer'),
                                            new OA\Property(property: 'name', type: 'string'),
                                        ]
                                    ),
                                    new OA\Property(
                                        property: 'status',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id',    type: 'integer'),
                                            new OA\Property(property: 'label', type: 'string'),
                                        ]
                                    ),
                                    new OA\Property(
                                        property: 'images',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'id',        type: 'integer'),
                                                new OA\Property(property: 'url',       type: 'string'),
                                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                            ]
                                        )
                                    ),
                                    new OA\Property(
                                        property: 'comments',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'id',        type: 'integer'),
                                                new OA\Property(property: 'author',    type: 'string'),
                                                new OA\Property(property: 'content',   type: 'string'),
                                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                            ]
                                        )
                                    ),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'page',  type: 'integer'),
                                new OA\Property(property: 'limit', type: 'integer'),
                                new OA\Property(property: 'pages', type: 'integer'),
                            ]
                        ),
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page      = max(1, (int)$request->query->get('page',  1));
        $limit     = min(100, max(1, (int)$request->query->get('limit', 10)));
        $filters   = [
            'category' => $request->query->get('category'),
            'status'   => $request->query->get('status'),
            'title'    => $request->query->get('title')
        ];
        $sortField = $request->query->get('sort', 'createdAt');
        $sortOrder = strtoupper($request->query->get('order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $result = $this->reportService->listFiltered($filters, $page, $limit, $sortField, $sortOrder);
        return $this->json($result, 200);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Utwórz nowe zgłoszenie',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object',
                required: ['title','description','categoryId','statusId'],
                properties: [
                    new OA\Property(property: 'title',       type: 'string', example: 'Awaria latarni'),
                    new OA\Property(property: 'description', type: 'string', example: 'Latarnia przy ul. X nie świeci.'),
                    new OA\Property(property: 'categoryId',  type: 'integer', example: 2),
                    new OA\Property(property: 'statusId',    type: 'integer', example: 1),
                    new OA\Property(property: 'latitude',    type: 'number', format: 'float', example: 50.06143),
                    new OA\Property(property: 'longitude',   type: 'number', format: 'float', example: 19.93658),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Zgłoszenie utworzone',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',          type: 'integer'),
                        new OA\Property(property: 'title',       type: 'string'),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'createdAt',   type: 'string', format: 'date-time'),
                        new OA\Property(property: 'latitude',    type: 'string', example: '50.06143'),
                        new OA\Property(property: 'longitude',   type: 'string', example: '19.93658'),
                        new OA\Property(
                            property: 'category',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id',   type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                            ]
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id',    type: 'integer'),
                                new OA\Property(property: 'label', type: 'string'),
                            ]
                        ),
                        new OA\Property(
                            property: 'images',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',        type: 'integer'),
                                    new OA\Property(property: 'url',       type: 'string'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'comments',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',        type: 'integer'),
                                    new OA\Property(property: 'author',    type: 'string'),
                                    new OA\Property(property: 'content',   type: 'string'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe (brak wymaganego pola)',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'title, description, categoryId, statusId are required')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Brak kategorii lub statusu o podanym ID',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Category not found')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Użytkownik niezatwierdzony',
                content: new OA\JsonContent(
                    properties: [ new OA\Property(property: 'error', type: 'string', example: 'Account not approved by admin') ]
                )
            ),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(CanReportVoter::CAN_REPORT);

        try {
            $data = json_decode($request->getContent(), true);
            $current = $this->getUser();

            $r = $this->reportService->create($data, $current);

            return $this->json($this->reportService->serialize($r), 201);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz szczegóły zgłoszenia',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID zgłoszenia',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca szczegóły zgłoszenia',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',          type: 'integer'),
                        new OA\Property(property: 'title',       type: 'string'),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'createdAt',   type: 'string', format: 'date-time'),
                        new OA\Property(property: 'latitude',    type: 'string', example: '50.06143'),
                        new OA\Property(property: 'longitude',   type: 'string', example: '19.93658'),
                        new OA\Property(
                            property: 'category',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id',   type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                            ]
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id',    type: 'integer'),
                                new OA\Property(property: 'label', type: 'string'),
                            ]
                        ),
                        new OA\Property(
                            property: 'images',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',        type: 'integer'),
                                    new OA\Property(property: 'url',       type: 'string'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'comments',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',        type: 'integer'),
                                    new OA\Property(property: 'author',    type: 'string'),
                                    new OA\Property(property: 'content',   type: 'string'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Zgłoszenie nie znalezione',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not found')
                    ]
                )
            ),
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

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Aktualizuj istniejące zgłoszenie',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID zgłoszenia',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object',
                properties: [
                    new OA\Property(property: 'title',      type: 'string',  example: 'Nowy tytuł'),
                    new OA\Property(property: 'description',type: 'string',  example: 'Zaktualizowany opis'),
                    new OA\Property(property: 'categoryId', type: 'integer', example: 3),
                    new OA\Property(property: 'statusId',   type: 'integer', example: 2),
                    new OA\Property(property: 'latitude',   type: 'number',  format: 'float', example: 50.06465),
                    new OA\Property(property: 'longitude',  type: 'number',  format: 'float', example: 19.94498),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zgłoszenie zaktualizowane pomyślnie',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',          type: 'integer'),
                        new OA\Property(property: 'title',       type: 'string'),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'createdAt',   type: 'string', format: 'date-time'),
                        new OA\Property(property: 'latitude',    type: 'string', example: '50.06465'),
                        new OA\Property(property: 'longitude',   type: 'string', example: '19.94498'),
                        new OA\Property(
                            property: 'category',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id',    type: 'integer'),
                                new OA\Property(property: 'name',  type: 'string'),
                            ]
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id',    type: 'integer'),
                                new OA\Property(property: 'label', type: 'string'),
                            ]
                        ),
                        new OA\Property(
                            property: 'images',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',        type: 'integer'),
                                    new OA\Property(property: 'url',       type: 'string'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'comments',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',        type: 'integer'),
                                    new OA\Property(property: 'author',    type: 'string'),
                                    new OA\Property(property: 'content',   type: 'string'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe / brak ciała żądania',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'No data provided')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Zgłoszenie, kategoria lub status nie znalezione',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not found')
                    ]
                )
            ),
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
        try {
            $updated = $this->reportService->update($report, $data);
            return $this->json($this->reportService->serialize($updated), 200);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń zgłoszenie',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID zgłoszenia do usunięcia',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Zgłoszenie usunięte'
            ),
            new OA\Response(
                response: 404,
                description: 'Zgłoszenie nie znalezione',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not found')
                    ]
                )
            ),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $report = $this->reportRepo->find($id);
        if (!$report) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $this->reportService->delete($report);
        return $this->json(null, 204);
    }
}
