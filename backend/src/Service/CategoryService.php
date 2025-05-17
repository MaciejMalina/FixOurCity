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
        private CategoryRepository     $catRepo
    ) {}

    public function listFiltered(
        array  $filters   = [],
        int    $page      = 1,
        int    $limit     = 10,
        string $sortField = 'name',
        string $sortOrder = 'ASC'
    ): array {
        $items = $this->catRepo->findFiltered($filters, $page, $limit, $sortField, $sortOrder);
        $total = $this->catRepo->countFiltered($filters);

        $data = array_map(fn(Category $c) => [
            'id'   => $c->getId(),
            'name' => $c->getName(),
        ], $items);

        return ['data'=>$data,'meta'=>['total'=>$total,'page'=>$page,'limit'=>$limit]];
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
        $c = $this->catRepo->find($id);
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
        $c = $this->catRepo->find($id);
        if (!$c) {
            throw new NotFoundHttpException('Category not found');
        }
        $this->em->remove($c);
        $this->em->flush();
    }
}
