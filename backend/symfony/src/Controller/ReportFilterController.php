<?php
namespace App\Controller;

use App\Entity\ReportFilter;
use App\Repository\ReportFilterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/filter')]
class ReportFilterController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(ReportFilterRepository $repository): JsonResponse
    {
        return $this->json($repository->findAll());
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(ReportFilterRepository $repository, int $id): JsonResponse
    {
        $filter = $repository->find($id);
        return $filter ? $this->json($filter) : $this->json(['error' => 'Not found'], 404);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $filter = new ReportFilter();
        $filter->setLabel($data['label']);
        $em->persist($filter);
        $em->flush();

        return $this->json($filter, 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, ReportFilterRepository $repo, EntityManagerInterface $em, int $id): JsonResponse
    {
        $filter = $repo->find($id);
        if (!$filter) return $this->json(['error' => 'Not found'], 404);

        $data = json_decode($request->getContent(), true);
        $filter->setLabel($data['label'] ?? $filter->getLabel());
        $em->flush();

        return $this->json($filter);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(ReportFilterRepository $repo, EntityManagerInterface $em, int $id): JsonResponse
    {
        $filter = $repo->find($id);
        if (!$filter) return $this->json(['error' => 'Not found'], 404);

        $em->remove($filter);
        $em->flush();
        return $this->json(null, 204);
    }
}
