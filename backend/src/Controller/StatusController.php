<?php
namespace App\Controller;

use App\Service\StatusService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Statuses')]
#[Route(path: '/api/v1/statuses')]
class StatusController extends AbstractController
{
    public function __construct(private StatusService $statusService) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista statusów (paginacja, filtrowanie po nazwie, sortowanie)',
        parameters: [
            new OA\Parameter(
                name: 'label',
                in: 'query',
                description: 'Filtruj po etykiecie (fragment, ILIKE)',
                schema: new OA\Schema(type: 'string'),
                example: 'Nowe'
            ),
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
                description: 'Liczba elementów na stronę (max 100)',
                schema: new OA\Schema(type: 'integer'),
                example: 10
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                description: 'Pole sortowania (dozwolone: label)',
                schema: new OA\Schema(type: 'string'),
                example: 'label'
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                description: 'Kierunek sortowania (ASC lub DESC)',
                schema: new OA\Schema(type: 'string', enum: ['ASC','DESC']),
                example: 'ASC'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista statusów wraz z metadanymi paginacji',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',    type: 'integer'),
                                    new OA\Property(property: 'label', type: 'string'),
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
                            ]
                        ),
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = ['label' => $request->query->get('label')];
        $page    = max(1, (int)$request->query->get('page',  1));
        $limit   = min(100, max(1, (int)$request->query->get('limit', 10)));
        $sort    = $request->query->get('sort', 'label');
        $order   = strtoupper($request->query->get('order', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $result = $this->statusService->listFiltered($filters, $page, $limit, $sort, $order);
        return $this->json($result, 200);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Utwórz nowy status',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object',
                required: ['label'],
                properties: [
                    new OA\Property(property: 'label', type: 'string', example: 'Nowe')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Status utworzony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',    type: 'integer'),
                        new OA\Property(property: 'label', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe (brak pola label)',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Label is required')
                    ]
                )
            ),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $data   = json_decode($request->getContent(), true);
            $status = $this->statusService->create($data);
            return $this->json(['id' => $status->getId(), 'label' => $status->getLabel()], 201);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz status po ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID statusu',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status znaleziony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',    type: 'integer'),
                        new OA\Property(property: 'label', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Status nie znaleziony',
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
        try {
            $all   = $this->statusService->listFiltered([], 1, PHP_INT_MAX);
            $found = array_filter($all['data'], fn($s) => $s['id'] === $id);
            if (empty($found)) {
                return $this->json(['error' => 'Not found'], 404);
            }
            return $this->json(array_values($found)[0], 200);
        } catch (\Throwable) {
            return $this->json(['error' => 'Not found'], 404);
        }
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Zaktualizuj status',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID statusu',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object',
                required: ['label'],
                properties: [
                    new OA\Property(property: 'label', type: 'string', example: 'Zaktualizowany')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status zaktualizowany',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',    type: 'integer'),
                        new OA\Property(property: 'label', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Label is required')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Status nie znaleziony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Status not found')
                    ]
                )
            ),
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data   = json_decode($request->getContent(), true);
            $status = $this->statusService->update($id, $data);
            return $this->json(['id' => $status->getId(), 'label' => $status->getLabel()], 200);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń status',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID statusu do usunięcia',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Status usunięty'
            ),
            new OA\Response(
                response: 404,
                description: 'Status nie znaleziony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Status not found')
                    ]
                )
            ),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->statusService->delete($id);
            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }
}
