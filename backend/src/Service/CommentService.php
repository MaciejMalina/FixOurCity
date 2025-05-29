<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CommentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReportRepository       $reportRepository,
        private CommentRepository      $commentRepository
    ) {}

    /**
     * Tworzy nowy komentarz dla podanego reportId i użytkownika.
     *
     * @throws BadRequestHttpException jeśli brakuje danych
     * @throws NotFoundHttpException   jeśli nie ma raportu o danym ID
     */
    public function create(array $data, User $user): Comment
    {
        if (empty($data['reportId']) || empty($data['content'])) {
            throw new BadRequestHttpException('Missing reportId or content');
        }

        $report = $this->reportRepository->find($data['reportId']);
        if (!$report) {
            throw new NotFoundHttpException('Report not found');
        }

        $comment = new Comment();
        $comment
            ->setContent($data['content'])
            ->setUser($user)
            ->setReport($report);

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    /**
     * Aktualizuje treść istniejącego komentarza.
     *
     * @throws BadRequestHttpException jeśli brak content w danych
     */
    public function update(Comment $comment, array $data): Comment
    {
        if (!isset($data['content']) || $data['content'] === '') {
            throw new BadRequestHttpException('Content must be provided');
        }

        $comment->setContent($data['content']);
        $this->em->flush();

        return $comment;
    }

    public function delete(Comment $comment): void
    {
        $this->em->remove($comment);
        $this->em->flush();
    }

    /**
     * Zwraca paginowaną i posortowaną listę komentarzy oraz metadane.
     *
     * @param array{reportId?:int} $filters
     */
    public function listFiltered(
        array  $filters   = [],
        int    $page      = 1,
        int    $limit     = 10,
        string $sortField = 'createdAt',
        string $sortOrder = 'DESC'
    ): array {
        $items = $this->commentRepository->findFiltered($filters, $page, $limit, $sortField, $sortOrder);
        $total = $this->commentRepository->countFiltered($filters);

        return [
            'data' => array_map(fn(Comment $c) => [
                'id'        => $c->getId(),
                'content'   => $c->getContent(),
                'createdAt' => $c->getCreatedAt()->format('c'),
            ], $items),
            'meta' => [
                'total' => $total,
                'page'  => $page,
                'limit' => $limit,
            ],
        ];
    }
}
