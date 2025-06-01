<?php
namespace App\Service;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ImageService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReportRepository $reportRepo,
        private ImageRepository $imageRepo
    ) {}

    public function create(array $data): Image
    {
        if (empty($data['reportId']) || empty($data['url'])) {
            throw new BadRequestHttpException('reportId and url are required');
        }
        $report = $this->reportRepo->find($data['reportId']);
        if (!$report) {
            throw new NotFoundHttpException('Report not found');
        }
        $img = new Image();
        $img->setUrl($data['url'])
            ->setReport($report);
        $this->em->persist($img);
        $this->em->flush();
        return $img;
    }

    public function update(int $id, array $data): Image
    {
        $img = $this->imageRepo->find($id);
        if (!$img) {
            throw new NotFoundHttpException('Image not found');
        }
        if (isset($data['url'])) {
            $img->setUrl($data['url']);
        }
        $this->em->flush();
        return $img;
    }

    public function delete(int $id): void
    {
        $img = $this->imageRepo->find($id);
        if (!$img) {
            throw new NotFoundHttpException('Image not found');
        }
        $this->em->remove($img);
        $this->em->flush();
    }

    public function serialize(Image $i): array
    {
        return [
            'id'        => $i->getId(),
            'url'       => $i->getUrl(),
            'createdAt' => $i->getCreatedAt()->format('c'),
            'reportId'  => $i->getReport()->getId(),
        ];
    }

    public function listFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'createdAt',
        string $sortOrder = 'DESC'
    ): array {
        $items = $this->imageRepo->findFiltered($filters, $page, $limit, $sortField, $sortOrder);
        $total = $this->imageRepo->countFiltered($filters);

        $data = [];
        foreach ($items as $i) {
            $data[] = $this->serialize($i);
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
