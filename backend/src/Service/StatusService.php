<?php

namespace App\Service;

use App\Entity\Status;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StatusService
{
    public function __construct(
        private EntityManagerInterface $em,
        private StatusRepository       $statusRepo
    ) {}

    public function listFiltered(
        array  $filters   = [],
        int    $page      = 1,
        int    $limit     = 10,
        string $sortField = 'label',
        string $sortOrder = 'ASC'
    ): array {
        $items = $this->statusRepo->findFiltered($filters, $page, $limit, $sortField, $sortOrder);
        $total = $this->statusRepo->countFiltered($filters);

        $data = array_map(fn(Status $s) => [
            'id'    => $s->getId(),
            'label' => $s->getLabel(),
        ], $items);

        return ['data' => $data, 'meta' => ['total' => $total, 'page' => $page, 'limit' => $limit]];
    }

    public function create(array $data): Status
    {
        if (empty($data['label'])) {
            throw new BadRequestHttpException('Label is required');
        }

        $status = new Status();
        $status->setLabel($data['label']);
        $this->em->persist($status);
        $this->em->flush();

        return $status;
    }

    public function update(int $id, array $data): Status
    {
        $status = $this->statusRepo->find($id);
        if (!$status) {
            throw new NotFoundHttpException('Status not found');
        }

        if (empty($data['label'])) {
            throw new BadRequestHttpException('Label is required');
        }

        $status->setLabel($data['label']);
        $this->em->flush();

        return $status;
    }

    public function delete(int $id): void
    {
        $status = $this->statusRepo->find($id);
        if (!$status) {
            throw new NotFoundHttpException('Status not found');
        }

        $this->em->remove($status);
        $this->em->flush();
    }
}
