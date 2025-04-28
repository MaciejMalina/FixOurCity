<?php

namespace App\Controller;

use App\Service\CommentService;
use App\Repository\ReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
