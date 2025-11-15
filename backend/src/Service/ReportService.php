<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Report;
use App\Repository\ReportRepository;
use App\Repository\CategoryRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ReportService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReportRepository $reportRepo,
        private CategoryRepository $categoryRepo,
        private StatusRepository $statusRepo
    ) {}

    public function create(array $data, User $author): Report
    {
        if (empty($data['title']) || empty($data['description']) 
            || empty($data['categoryId']) || empty($data['statusId'])) {
            throw new BadRequestHttpException('title, description, categoryId, statusId are required');
        }

        $cat = $this->categoryRepo->find($data['categoryId']) 
            ?? throw new NotFoundHttpException('Category not found');
        $st  = $this->statusRepo->find($data['statusId']) 
            ?? throw new NotFoundHttpException('Status not found');

        $r = new Report();
        $r->setTitle($data['title'])
        ->setDescription($data['description'])
        ->setCategory($cat)
        ->setStatus($st)
        ->setUser($author);

        if (isset($data['latitude']))  { $r->setLatitude((string)$data['latitude']); }
        if (isset($data['longitude'])) { $r->setLongitude((string)$data['longitude']); }

        $this->em->persist($r);
        $this->em->flush();

        return $r;
    }

    public function update(Report $r, array $data): Report
    {
        if (isset($data['title'])) {
            $r->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $r->setDescription($data['description']);
        }
        if (isset($data['categoryId'])) {
            $cat = $this->categoryRepo->find($data['categoryId']);
            if (!$cat) {
                throw new NotFoundHttpException('Category not found');
            }
            $r->setCategory($cat);
        }
        if (isset($data['statusId'])) {
            $st = $this->statusRepo->find($data['statusId']);
            if (!$st) {
                throw new NotFoundHttpException('Status not found');
            }
            $r->setStatus($st);
        }
        if (array_key_exists('latitude', $data)) {
            $r->setLatitude($data['latitude'] !== null ? (string)$data['latitude'] : null);
        }
        if (array_key_exists('longitude', $data)) {
            $r->setLongitude($data['longitude'] !== null ? (string)$data['longitude'] : null);
        }

        $this->em->flush();
        return $r;
    }

    public function delete(Report $r): void
    {
        $this->em->remove($r);
        $this->em->flush();
    }

    public function serialize(Report $r): array
    {
        $images = [];
        foreach ($r->getImages() as $img) {
            $images[] = [
                'id'        => $img->getId(),
                'url'       => $img->getUrl(),
                'createdAt' => $img->getCreatedAt()->format('c'),
            ];
        }

        $comments = [];
        foreach ($r->getComments() as $c) {
            $comments[] = [
                'id'        => $c->getId(),
                'author'    => $c->getAuthor(),
                'content'   => $c->getContent(),
                'createdAt' => $c->getCreatedAt()->format('c'),
            ];
        }

        return [
            'id'           => $r->getId(),
            'title'        => $r->getTitle(),
            'description'  => $r->getDescription(),
            'createdAt'    => $r->getCreatedAt()->format('c'),
            'latitude'     => $r->getLatitude(),
            'longitude'    => $r->getLongitude(),
            'category'     => ['id' => $r->getCategory()->getId(), 'name' => $r->getCategory()->getName()],
            'status'       => ['id' => $r->getStatus()->getId(),      'label'=> $r->getStatus()->getLabel()],
            'images'       => $images,
            'comments'     => $comments,
            'author' => [
                'id' => $r->getUser()->getId(),
                'email' => $r->getUser()->getEmail(),
                'firstName' => $r->getUser()->getFirstName(),
                'lastName'  => $r->getUser()->getLastName(),
                ],
            ];
    }

    public function listFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'createdAt',
        string $sortOrder = 'DESC'
    ): array {
        $items = $this->reportRepo->findFiltered($filters, $page, $limit, $sortField, $sortOrder);
        $total = $this->reportRepo->countFiltered($filters);

        $data = [];
        foreach ($items as $r) {
            $data[] = $this->serialize($r);
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
