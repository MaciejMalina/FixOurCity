<?php
namespace App\Controller;

use App\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Images')]
#[Route(path: '/api/v1/images')]
class ImageController extends AbstractController
{
    public function __construct(private ImageService $imageService) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista obrazów (paginacja, filtrowanie po reportId, sortowanie)',
        parameters: [
            new OA\Parameter(
                name: 'reportId',
                in: 'query',
                description: 'ID zgłoszenia do filtrowania',
                schema: new OA\Schema(type: 'integer'),
                example: 5
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
                description: 'Liczba elementów na stronę',
                schema: new OA\Schema(type: 'integer'),
                example: 10
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                description: 'Pole sortowania (dozwolone: createdAt)',
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
                description: 'Zwraca obiekt data + meta',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',        type: 'integer'),
                                    new OA\Property(property: 'url',       type: 'string'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'reportId',  type: 'integer'),
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
        $filters = [];
        if ($r = $request->query->get('reportId')) {
            $filters['reportId'] = (int)$r;
        }
        $page  = max(1, (int)$request->query->get('page',  1));
        $limit = min(100, max(1, (int)$request->query->get('limit', 10)));
        $sort  = $request->query->get('sort', 'createdAt');
        $order = strtoupper($request->query->get('order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $result = $this->imageService->listFiltered($filters, $page, $limit, $sort, $order);
        return $this->json($result, 200);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaj nowe zdjęcie do zgłoszenia',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object',
                required: ['reportId','url'],
                properties: [
                    new OA\Property(property: 'reportId', type: 'integer', example: 5),
                    new OA\Property(property: 'url',      type: 'string',  example: 'https://.../img.png'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Obraz utworzony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',        type: 'integer'),
                        new OA\Property(property: 'url',       type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'reportId',  type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe (brak reportId lub url)',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'reportId and url are required')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Zgłoszenie nie znalezione',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Report not found')
                    ]
                )
            ),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $img  = $this->imageService->create($data);
            return $this->json($this->imageService->serialize($img), 201);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz konkretne zdjęcie po ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID obrazu',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Obraz znaleziony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',        type: 'integer'),
                        new OA\Property(property: 'url',       type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'reportId',  type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Obraz nie znaleziony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Image not found')
                    ]
                )
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $all   = $this->imageService->listFiltered([], 1, PHP_INT_MAX);
            $found = array_filter($all['data'], fn($i) => $i['id'] === $id);
            if (empty($found)) {
                throw new NotFoundHttpException('Image not found');
            }
            return $this->json(array_values($found)[0], 200);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Zaktualizuj obraz (zmiana URL)',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID obrazu',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object',
                required: ['url'],
                properties: [
                    new OA\Property(property: 'url', type: 'string', example: 'https://.../new.png')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Obraz zaktualizowany',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',        type: 'integer'),
                        new OA\Property(property: 'url',       type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'reportId',  type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe (brak pola url)',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'reportId and url are required')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Obraz nie znaleziony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Image not found')
                    ]
                )
            ),
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $img  = $this->imageService->update($id, $data);
            return $this->json($this->imageService->serialize($img), 200);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń obraz',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID obrazu do usunięcia',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Obraz usunięty'
            ),
            new OA\Response(
                response: 404,
                description: 'Obraz nie znaleziony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Image not found')
                    ]
                )
            ),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->imageService->delete($id);
            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }
}
