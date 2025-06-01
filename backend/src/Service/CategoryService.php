<?php
namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CategoryService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepo
    ) {}

    public function listFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'name',
        string $sortOrder = 'ASC'
    ): array {
        $items = $this->categoryRepo->findFiltered($filters, $page, $limit, $sortField, $sortOrder);
        $total = $this->categoryRepo->countFiltered($filters);

        $data = [];
        foreach ($items as $c) {
            $data[] = [
                'id'   => $c->getId(),
                'name' => $c->getName(),
            ];
        }

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page'  => $page,
                'limit' => $limit,
            ],
        ];
    }

    public function create(array $data): Category
    {
        if (empty($data['name'])) {
            throw new BadRequestHttpException('Name is required');
        }
        $c = new Category();
        $c->setName($data['name']);
        $this->em->persist($c);
        $this->em->flush();
        return $c;
    }

    public function update(int $id, array $data): Category
    {
        $c = $this->categoryRepo->find($id);
        if (!$c) {
            throw new NotFoundHttpException('Category not found');
        }
        if (empty($data['name'])) {
            throw new BadRequestHttpException('Name is required');
        }
        $c->setName($data['name']);
        $this->em->flush();
        return $c;
    }

    public function delete(int $id): void
    {
        $c = $this->categoryRepo->find($id);
        if (!$c) {
            throw new NotFoundHttpException('Category not found');
        }
        $this->em->remove($c);
        $this->em->flush();
    }

    public function serialize(Category $c): array
    {
        return ['id' => $c->getId(), 'name' => $c->getName()];
    }
}
