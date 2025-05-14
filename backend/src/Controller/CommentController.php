<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Comments')]
#[Route(path: '/api/v1/comments')]
class CommentController extends AbstractController
{
    public function __construct(
        private CommentRepository $commentRepository,
        private CommentService    $commentService
    ) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz listę komentarzy z paginacją, filtrowaniem i sortowaniem',
        parameters: [
            new OA\Parameter(name: 'reportId', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), description: 'ID zgłoszenia do filtrowania'),
            new OA\Parameter(name: 'page',     in: 'query', required: false, schema: new OA\Schema(type: 'integer'), example: 1, description: 'Numer strony'),
            new OA\Parameter(name: 'limit',    in: 'query', required: false, schema: new OA\Schema(type: 'integer'), example: 10, description: 'Liczba komentarzy na stronę'),
            new OA\Parameter(name: 'sort',     in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'createdAt', description: 'Pole sortowania'),
            new OA\Parameter(name: 'order',    in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['ASC','DESC']), example: 'DESC', description: 'Kierunek sortowania'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca listę komentarzy wraz z metadanymi paginacji',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id',        type: 'integer'),
                                new OA\Property(property: 'content',   type: 'string'),
                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
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
    public function index(Request $request): JsonResponse
    {
        $reportId = $request->query->get('reportId');
        $page     = max(1, (int)$request->query->get('page', 1));
        $limit    = min(100, max(1, (int)$request->query->get('limit', 10)));
        $sort     = $request->query->get('sort', 'createdAt');
        $order    = strtoupper($request->query->get('order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        if ($reportId) {
            $comments = $this->commentRepository->findBy(
                ['report' => $reportId],
                [$sort => $order],
                $limit,
                ($page - 1) * $limit
            );
            $total = count($this->commentRepository->findBy(['report' => $reportId]));
        } else {
            $comments = $this->commentRepository->findBy(
                [],
                [$sort => $order],
                $limit,
                ($page - 1) * $limit
            );
            $total = count($this->commentRepository->findAll());
        }

        $data = array_map(fn(Comment $c) => $this->serialize($c), $comments);

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
        summary: 'Utwórz komentarz',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reportId','content'],
                properties: [
                    new OA\Property(property: 'reportId', type: 'integer', example: 42),
                    new OA\Property(property: 'content',  type: 'string',  example: 'Dziękuję za szybką naprawę!')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Komentarz utworzony'),
            new OA\Response(response: 400, description: 'Brak wymaganych pól')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['reportId']) || empty($data['content'])) {
            return $this->json(['error' => 'Missing fields'], 400);
        }

        $comment = $this->commentService->create($data, $this->getUser());
        return $this->json($this->serialize($comment), 201);
    }

    #[Route(path: '/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz szczegóły komentarza',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Komentarz znaleziony',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id',        type: 'integer'),
                        new OA\Property(property: 'content',   type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Komentarz nie znaleziony')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json($this->serialize($comment), 200);
    }

    #[Route(path: '/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Aktualizuj komentarz',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Zaktualizowana treść')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Komentarz zaktualizowany'),
            new OA\Response(response: 400, description: 'Brak danych'),
            new OA\Response(response: 404, description: 'Komentarz nie znaleziony')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (empty($data['content'])) {
            return $this->json(['error' => 'No data provided'], 400);
        }

        $updated = $this->commentService->update($comment, $data);
        return $this->json($this->serialize($updated), 200);
    }

    #[Route(path: '/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń komentarz',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Komentarz usunięty'),
            new OA\Response(response: 404, description: 'Komentarz nie znaleziony')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $this->commentService->delete($comment);
        return $this->json(null, 204);
    }

    private function serialize(Comment $comment): array
    {
        return [
            'id'        => $comment->getId(),
            'content'   => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('c'),
        ];
    }
}
