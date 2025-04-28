<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Report;
use Doctrine\ORM\EntityManagerInterface;

class CommentService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createComment(Report $report, string $content): Comment
    {
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setReport($report);

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }
}
