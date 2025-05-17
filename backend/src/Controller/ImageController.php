<?php

namespace App\Controller;

use App\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[OA\Tag(name: 'Images')]
#[Route(path: '/api/v1/images')]
class ImageController extends AbstractController
{
    public function __construct(private ImageService $imageService) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista obrazów (paginacja, filtrowanie po reportId, sortowanie)',
        parameters: [
            new OA\Parameter(name: 'reportId', in: 'query', schema: new OA\Schema(type: 'integer'), description: 'ID zgłoszenia'),
            new OA\Parameter(name: 'page',     in: 'query', schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\Parameter(name: 'limit',    in: 'query', schema: new OA\Schema(type: 'integer'), example: 10),
            new OA\Parameter(name: 'sort',     in: 'query', schema: new OA\Schema(type: 'string'), example: 'createdAt'),
            new OA\Parameter(name: 'order',    in: 'query', schema: new OA\Schema(type: 'string', enum: ['ASC','DESC']), example: 'DESC'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Zwraca data + meta')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = [];
        if ($r = $request->query->get('reportId')) {
            $filters['reportId'] = (int)$r;
        }
        $page  = max(1, (int)$request->query->get('page', 1));
        $limit = min(100, max(1, (int)$request->query->get('limit', 10)));
        $sort  = $request->query->get('sort', 'createdAt');
        $order = strtoupper($request->query->get('order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $result = $this->imageService->listFiltered($filters, $page, $limit, $sort, $order);
        return $this->json($result, 200);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Utwórz obraz',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reportId','url'],
                properties: [
                    new OA\Property(property: 'reportId', type: 'integer', example: 42),
                    new OA\Property(property: 'url',      type: 'string',  example: 'https://.../img.png'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Obraz utworzony'),
            new OA\Response(response: 400, description: 'Brak wymaganych pól'),
            new OA\Response(response: 404, description: 'Zgłoszenie nie znalezione')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $img  = $this->imageService->create($data);
            return $this->json($this->imageService->serialize($img), 201);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }

    #[Route(path: '/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz obraz po ID',
        parameters: [ new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')) ],
        responses: [
            new OA\Response(response: 200, description: 'Obraz znaleziony'),
            new OA\Response(response: 404, description: 'Obraz nie znaleziony')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $all   = $this->imageService->listFiltered([], 1, PHP_INT_MAX);
            $found = array_filter($all['data'], fn($i)=>$i['id']===$id);
            if (empty($found)) {
                throw new NotFoundHttpException('Image not found');
            }
            return $this->json(array_values($found)[0], 200);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }

    #[Route(path: '/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Aktualizuj obraz (url)',
        parameters: [ new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')) ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['url'],
                properties: [ new OA\Property(property: 'url', type: 'string', example: 'https://.../new.png') ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Obraz zaktualizowany'),
            new OA\Response(response: 400, description: 'Brak pola url'),
            new OA\Response(response: 404, description: 'Obraz nie znaleziony')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $img  = $this->imageService->update($id, $data);
            return $this->json($this->imageService->serialize($img), 200);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }

    #[Route(path: '/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń obraz',
        parameters: [ new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')) ],
        responses: [
            new OA\Response(response: 204, description: 'Obraz usunięty'),
            new OA\Response(response: 404, description: 'Obraz nie znaleziony')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->imageService->delete($id);
            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }
}
