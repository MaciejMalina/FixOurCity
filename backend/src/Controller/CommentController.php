<?php
namespace App\Controller;

use App\Service\CommentService;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Comments')]
#[Route(path: '/api/v1/comments')]
class CommentController extends AbstractController
{
    public function __construct(
        private CommentService    $commentService,
        private CommentRepository $commentRepo
    ) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz listę komentarzy (paginacja, filtrowanie po reportId, sortowanie)',
        parameters: [
            new OA\Parameter(
                name: 'reportId',
                in: 'query',
                description: 'Filtruj komentarze po ID zgłoszenia',
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
                description: 'Liczba komentarzy na stronę',
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
                                    new OA\Property(property: 'content',   type: 'string'),
                                    new OA\Property(property: 'author',    type: 'string'),
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

        $result = $this->commentService->listFiltered($filters, $page, $limit, $sort, $order);
        return $this->json($result, 200);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaj nowy komentarz do zgłoszenia',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object',
                required: ['reportId','content','author'],
                properties: [
                    new OA\Property(property: 'reportId', type: 'integer', example: 5),
                    new OA\Property(property: 'content',  type: 'string',  example: 'Dziękuję za szybką reakcję!'),
                    new OA\Property(property: 'author',   type: 'string',  example: 'Jan Kowalski'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Komentarz utworzony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',        type: 'integer'),
                        new OA\Property(property: 'content',   type: 'string'),
                        new OA\Property(property: 'author',    type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'reportId',  type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe (brak wymaganych pól)',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'reportId, content and author are required')
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
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not authenticated')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Brak uprawnień (Access Denied)',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Access Denied')
                    ]
                )
            ),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $c = $this->commentService->create($data);
            return $this->json($this->commentService->serialize($c), 201);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz komentarz po ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID komentarza',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Komentarz znaleziony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',        type: 'integer'),
                        new OA\Property(property: 'content',   type: 'string'),
                        new OA\Property(property: 'author',    type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'reportId',  type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Komentarz nie znaleziony',
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
        $c = $this->commentRepo->find($id);
        if (!$c) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json($this->commentService->serialize($c), 200);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Edytuj istniejący komentarz',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID komentarza',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object',
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Nowa treść komentarza')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Komentarz zaktualizowany',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'id',        type: 'integer'),
                        new OA\Property(property: 'content',   type: 'string'),
                        new OA\Property(property: 'author',    type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'reportId',  type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Błędne dane wejściowe (brak content)',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Content must be provided')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Komentarz nie znaleziony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not found')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not authenticated')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Brak uprawnień (Access Denied)',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Access Denied')
                    ]
                )
            ),
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $c = $this->commentRepo->find($id);
        if (!$c) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $data = json_decode($request->getContent(), true);
        if (empty($data['content'])) {
            return $this->json(['error' => 'No data provided'], 400);
        }
        try {
            $updated = $this->commentService->update($c, $data);
            return $this->json($this->commentService->serialize($updated), 200);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń komentarz',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID komentarza do usunięcia',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Komentarz usunięty'
            ),
            new OA\Response(
                response: 404,
                description: 'Komentarz nie znaleziony',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not found')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not authenticated')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Brak uprawnień (Access Denied)',
                content: new OA\JsonContent(type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Access Denied')
                    ]
                )
            ),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $c = $this->commentRepo->find($id);
        if (!$c) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $this->commentService->delete($c);
        return $this->json(null, 204);
    }
}
