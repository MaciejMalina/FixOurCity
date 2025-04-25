<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/comment')]
class CommentController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(CommentRepository $repository): JsonResponse
    {
        return $this->json($repository->findAll());
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(CommentRepository $repository, int $id): JsonResponse
    {
        $comment = $repository->find($id);
        return $comment ? $this->json($comment) : $this->json(['error' => 'Not found'], 404);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo, ReportRepository $reportRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $userRepo->find($data['user_id']);
        $report = $reportRepo->find($data['report_id']);

        $comment = new Comment();
        $comment->setUser($user);
        $comment->setReport($report);
        $comment->setContent($data['content']);

        $em->persist($comment);
        $em->flush();

        return $this->json($comment, 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, CommentRepository $repo, EntityManagerInterface $em, int $id): JsonResponse
    {
        $comment = $repo->find($id);
        if (!$comment) return $this->json(['error' => 'Not found'], 404);

        $data = json_decode($request->getContent(), true);
        $comment->setContent($data['content'] ?? $comment->getContent());
        $em->flush();

        return $this->json($comment);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(CommentRepository $repo, EntityManagerInterface $em, int $id): JsonResponse
    {
        $comment = $repo->find($id);
        if (!$comment) return $this->json(['error' => 'Not found'], 404);

        $em->remove($comment);
        $em->flush();
        return $this->json(null, 204);
    }
}
