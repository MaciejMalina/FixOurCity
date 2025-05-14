<?php

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TagService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TagRepository          $tagRepo
    ) {}

    public function listFiltered(
        array  $filters   = [],
        int    $page      = 1,
        int    $limit     = 10,
        string $sortField = 'name',
        string $sortOrder = 'ASC'
    ): array {
        $items = $this->tagRepo->findFiltered($filters, $page, $limit, $sortField, $sortOrder);
        $total = $this->tagRepo->countFiltered($filters);

        $data = array_map(fn(Tag $t) => [
            'id'   => $t->getId(),
            'name' => $t->getName(),
        ], $items);

        return ['data' => $data, 'meta' => ['total' => $total, 'page' => $page, 'limit' => $limit]];
    }

    public function create(array $data): Tag
    {
        if (empty($data['name'])) {
            throw new BadRequestHttpException('Name is required');
        }
        $tag = new Tag();
        $tag->setName($data['name']);
        $this->em->persist($tag);
        $this->em->flush();
        return $tag;
    }

    public function update(int $id, array $data): Tag
    {
        $tag = $this->tagRepo->find($id);
        if (!$tag) {
            throw new NotFoundHttpException('Tag not found');
        }
        if (empty($data['name'])) {
            throw new BadRequestHttpException('Name is required');
        }
        $tag->setName($data['name']);
        $this->em->flush();
        return $tag;
    }

    public function delete(int $id): void
    {
        $tag = $this->tagRepo->find($id);
        if (!$tag) {
            throw new NotFoundHttpException('Tag not found');
        }
        $this->em->remove($tag);
        $this->em->flush();
    }

    public function serialize(Tag $t): array
    {
        return ['id' => $t->getId(), 'name' => $t->getName()];
    }
}
