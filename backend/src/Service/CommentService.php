<?php
namespace App\Service;

use App\Entity\Comment;
use App\Entity\Report;
use App\Repository\CommentRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CommentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReportRepository $reportRepo,
        private CommentRepository $commentRepo
    ) {}

    public function create(array $data): Comment
    {
        if (empty($data['reportId']) || empty($data['content']) || empty($data['author'])) {
            throw new BadRequestHttpException('reportId, content and author are required');
        }
        $report = $this->reportRepo->find($data['reportId']);
        if (!$report) {
            throw new NotFoundHttpException('Report not found');
        }
        $c = new Comment();
        $c->setContent($data['content'])
          ->setAuthor($data['author'])
          ->setReport($report);

        $this->em->persist($c);
        $this->em->flush();

        return $c;
    }

    public function update(Comment $c, array $data): Comment
    {
        if (!isset($data['content']) || $data['content'] === '') {
            throw new BadRequestHttpException('Content must be provided');
        }
        $c->setContent($data['content']);
        $this->em->flush();
        return $c;
    }

    public function delete(Comment $c): void
    {
        $this->em->remove($c);
        $this->em->flush();
    }

    public function serialize(Comment $c): array
    {
        return [
            'id'        => $c->getId(),
            'content'   => $c->getContent(),
            'author'    => $c->getAuthor(),
            'createdAt' => $c->getCreatedAt()->format('c'),
            'reportId'  => $c->getReport()->getId(),
        ];
    }

    public function listFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'createdAt',
        string $sortOrder = 'DESC'
    ): array {
        $items = $this->commentRepo->findFiltered($filters, $page, $limit, $sortField, $sortOrder);
        $total = $this->commentRepo->countFiltered($filters);

        $data = [];
        foreach ($items as $c) {
            $data[] = $this->serialize($c);
        }

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page'  => $page,
                'limit' => $limit,
                'pages' => (int) ceil($total / $limit),
            ],
        ];
    }
}
