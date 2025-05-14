<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Comments')]
#[Route('/api/comments')]
class CommentController extends AbstractController
{
    public function __construct(
        private CommentRepository $commentRepository,
        private CommentService $commentService
    ) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista komentarzy (opcjonalnie filtrowana po reportId)',
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(
                name: 'reportId',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
                description: 'ID zgłoszenia, aby pobrać tylko jego komentarze'
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
                example: 1,
                description: 'Numer strony (10 komentarzy na stronę)'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista komentarzy',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'content', type: 'string'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        ]
                    )
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $reportId = $request->query->get('reportId');
        $page     = max(1, (int)$request->query->get('page', 1));

        if ($reportId) {
            $comments = $this->commentRepository->findBy(
                ['report' => $reportId],
                ['createdAt' => 'DESC'],
                10,
                ($page - 1) * 10
            );
        } else {
            $comments = $this->commentRepository->findBy(
                [],
                ['createdAt' => 'DESC'],
                10,
                ($page - 1) * 10
            );
        }

        return $this->json(array_map(fn(Comment $c) => $this->serialize($c), $comments));
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Utwórz komentarz',
        tags: ['Comments'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reportId', 'content'],
                properties: [
                    new OA\Property(property: 'reportId', type: 'integer', example: 42),
                    new OA\Property(property: 'content', type: 'string', example: 'Dziękuję za szybką naprawę!')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Komentarz utworzony',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'content', type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Brak wymaganych pól')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['reportId'], $data['content'])) {
            return $this->json(['error' => 'Missing fields'], 400);
        }

        $comment = $this->commentService->create($data, $this->getUser());
        return $this->json($this->serialize($comment), 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz szczegóły komentarza',
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                description: 'ID komentarza'
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Komentarz znaleziony',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'content', type: 'string'),
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
        return $comment
            ? $this->json($this->serialize($comment))
            : $this->json(['error' => 'Not found'], 404);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Aktualizuj komentarz',
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
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
            new OA\Response(
                response: 200,
                description: 'Komentarz zaktualizowany',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'content', type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Komentarz nie znaleziony')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $updated = $this->commentService->update($comment, $data);
        return $this->json($this->serialize($updated));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń komentarz',
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
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
