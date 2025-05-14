<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReportRepository $reportRepository
    ) {}

    public function create(array $data, User $user): Comment
    {
        $report = $this->reportRepository->find($data['reportId']);

        $comment = new Comment();
        $comment->setContent($data['content']);
        $comment->setUser($user);
        $comment->setReport($report);

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    public function update(Comment $comment, array $data): Comment
    {
        if (isset($data['content'])) {
            $comment->setContent($data['content']);
        }

        $this->em->flush();
        return $comment;
    }

    public function delete(Comment $comment): void
    {
        $this->em->remove($comment);
        $this->em->flush();
    }
}
