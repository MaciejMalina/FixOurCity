<?php

namespace App\Controller;

use App\Service\CommentService;
use App\Repository\ReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/comments')]
class CommentController extends AbstractController
{
    private CommentService $commentService;
    private ReportRepository $reportRepository;

    public function __construct(CommentService $commentService, ReportRepository $reportRepository)
    {
        $this->commentService = $commentService;
        $this->reportRepository = $reportRepository;
    }

    #[Route('/{reportId}', name: 'comment_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/comments/{reportId}',
        summary: 'Dodaj komentarz do zgłoszenia',
        description: 'Tworzy nowy komentarz przypisany do konkretnego zgłoszenia.',
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(
                name: 'reportId',
                in: 'path',
                required: true,
                description: 'ID zgłoszenia, do którego przypisujemy komentarz',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Treść komentarza')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Komentarz został utworzony',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'content', type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Brak treści komentarza'),
            new OA\Response(response: 404, description: 'Zgłoszenie nie znalezione')
        ]
    )]
    public function create(Request $request, int $reportId): JsonResponse
    {
        $report = $this->reportRepository->find($reportId);

        if (!$report) {
            return $this->json(['error' => 'Report not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['content'])) {
            return $this->json(['error' => 'Missing content'], 400);
        }

        $comment = $this->commentService->createComment($report, $data['content']);

        return $this->json([
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt(),
        ], 201);
    }
}
