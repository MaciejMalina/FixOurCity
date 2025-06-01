<?php
namespace App\Controller;

use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Categories')]
#[Route(path: '/api/v1/categories')]
class CategoryController extends AbstractController
{
    public function __construct(private CategoryService $catService) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista kategorii (paginacja, filtrowanie po nazwie, sortowanie)',
        parameters: [
            new OA\Parameter(
                name: 'name',
                in: 'query',
                description: 'Filtruj po nazwie (fragment, ILIKE)',
                schema: new OA\Schema(type: 'string')
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
                description: 'Pole sortowania (dozwolone: name)',
                schema: new OA\Schema(type: 'string'),
                example: 'name'
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
                description: 'Lista kategorii wraz z metadanymi paginacji',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',   type: 'integer'),
                                    new OA\Property(property: 'name', type: 'string'),
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
        $filters = ['name' => $request->query->get('name')];
        $page    = max(1, (int)$request->query->get('page',  1));
        $limit   = min(100, max(1, (int)$request->query->get('limit', 10)));
        $sort    = $request->query->get('sort', 'name');
        $order   = strtoupper($request->query->get('order', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $result = $this->catService->listFiltered($filters, $page, $limit, $sort, $order);
        return $this->json($result, 200);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Utwórz nową kategorię',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nowa kategoria')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Kategoria utworzona pomyślnie',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id',   type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe (brak pola name)',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Name is required')
                    ]
                )
            ),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $cat  = $this->catService->create($data);
            return $this->json($this->catService->serialize($cat), 201);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz kategorię po ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID kategorii',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Kategoria znaleziona',
                content: new OA\JsonContent(type: 'object', 
                    properties: [
                        new OA\Property(property: 'id',   type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Kategoria nie znaleziona',
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
            $all = $this->catService->listFiltered([], 1, PHP_INT_MAX);
            $found = array_filter($all['data'], fn($c) => $c['id'] === $id);
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
        summary: 'Zaktualizuj kategorię',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID kategorii',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Zmieniona kategoria')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Aktualizacja zakończona sukcesem',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',   type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Name is required')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Kategoria nie znaleziona',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Category not found')
                    ]
                )
            ),
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $cat  = $this->catService->update($id, $data);
            return $this->json($this->catService->serialize($cat), 200);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń kategorię',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID kategorii do usunięcia',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Kategoria usunięta'
            ),
            new OA\Response(
                response: 404,
                description: 'Kategoria nie znaleziona',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Category not found')
                    ]
                )
            ),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->catService->delete($id);
            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }
}
