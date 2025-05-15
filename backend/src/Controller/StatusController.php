<?php

namespace App\Controller;

use App\Service\StatusService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Statuses')]
#[Route(path: '/api/v1/statuses')]
class StatusController extends AbstractController
{
    public function __construct(private StatusService $statusService) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista statusów (paginacja, filtrowanie, sortowanie)',
        parameters: [
            new OA\Parameter(name: 'label', in: 'query', schema: new OA\Schema(type: 'string'), example: 'Nowe'),
            new OA\Parameter(name: 'page',  in: 'query', schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer'), example: 10),
            new OA\Parameter(name: 'sort',  in: 'query', schema: new OA\Schema(type: 'string'), example: 'label'),
            new OA\Parameter(name: 'order', in: 'query', schema: new OA\Schema(type: 'string', enum: ['ASC','DESC']), example: 'ASC'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Zwraca data + meta')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = ['label' => $request->query->get('label')];
        $page    = max(1, (int)$request->query->get('page', 1));
        $limit   = min(100, max(1, (int)$request->query->get('limit', 10)));
        $sort    = $request->query->get('sort', 'label');
        $order   = strtoupper($request->query->get('order', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $result = $this->statusService->listFiltered($filters, $page, $limit, $sort, $order);
        return $this->json($result, 200);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Utwórz nowy status',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['label'],
                properties: [
                    new OA\Property(property: 'label', type: 'string', example: 'Nowe')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Status utworzony'),
            new OA\Response(response: 400, description: 'Brak pola label')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data   = json_decode($request->getContent(), true);
        $status = $this->statusService->create($data);
        return $this->json(['id'=>$status->getId(),'label'=>$status->getLabel()], 201);
    }

    #[Route(path: '/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz status po ID',
        parameters: [ new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')) ],
        responses: [
            new OA\Response(response: 200, description: 'Status znaleziony'),
            new OA\Response(response: 404, description: 'Status nie znaleziony')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $all   = $this->statusService->listFiltered([], 1, PHP_INT_MAX);
            $found = array_filter($all['data'], fn($s)=>$s['id']===$id);
            if (empty($found)) {
                return $this->json(['error'=>'Not found'], 404);
            }
            return $this->json(array_values($found)[0], 200);
        } catch (\Throwable) {
            return $this->json(['error'=>'Not found'], 404);
        }
    }

    #[Route(path: '/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Aktualizuj status',
        parameters: [ new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')) ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['label'],
                properties: [ new OA\Property(property: 'label', type: 'string', example: 'Zaktualizowany') ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Status zaktualizowany'),
            new OA\Response(response: 400, description: 'Błędne dane'),
            new OA\Response(response: 404, description: 'Status nie znaleziony')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data   = json_decode($request->getContent(), true);
            $status = $this->statusService->update($id, $data);
            return $this->json(['id'=>$status->getId(),'label'=>$status->getLabel()], 200);
        } catch (\Symfony\Component\HttpKernel\Exception\BadRequestHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }

    #[Route(path: '/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń status',
        parameters: [ new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')) ],
        responses: [
            new OA\Response(response: 204, description: 'Status usunięty'),
            new OA\Response(response: 404, description: 'Status nie znaleziony')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->statusService->delete($id);
            return $this->json(null, 204);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }
}
