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
        private StatusRepository $statusRepo
    ) {}

    public function listFiltered(
        array $filters = [],
        int $page = 1,
        int $limit = 10,
        string $sortField = 'label',
        string $sortOrder = 'ASC'
    ): array {
        $items = $this->statusRepo->findFiltered($filters, $page, $limit, $sortField, $sortOrder);
        $total = $this->statusRepo->countFiltered($filters);

        $data = [];
        foreach ($items as $s) {
            $data[] = [
                'id'    => $s->getId(),
                'label' => $s->getLabel(),
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

    public function create(array $data): Status
    {
        if (empty($data['label'])) {
            throw new BadRequestHttpException('Label is required');
        }
        $s = new Status();
        $s->setLabel($data['label']);
        $this->em->persist($s);
        $this->em->flush();
        return $s;
    }

    public function update(int $id, array $data): Status
    {
        $s = $this->statusRepo->find($id);
        if (!$s) {
            throw new NotFoundHttpException('Status not found');
        }
        if (empty($data['label'])) {
            throw new BadRequestHttpException('Label is required');
        }
        $s->setLabel($data['label']);
        $this->em->flush();
        return $s;
    }

    public function delete(int $id): void
    {
        $s = $this->statusRepo->find($id);
        if (!$s) {
            throw new NotFoundHttpException('Status not found');
        }
        $this->em->remove($s);
        $this->em->flush();
    }

    public function serialize(Status $s): array
    {
        return ['id' => $s->getId(), 'label' => $s->getLabel()];
    }
}
