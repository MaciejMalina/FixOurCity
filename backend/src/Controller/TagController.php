<?php

namespace App\Controller;

use App\Service\TagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Tags')]
#[Route(path: '/api/v1/tags')]
class TagController extends AbstractController
{
    public function __construct(private TagService $tagService) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista tagów (paginacja, filtrowanie po nazwie, sortowanie)',
        parameters: [
            new OA\Parameter(name: 'name',  in: 'query', schema: new OA\Schema(type: 'string'), example: 'dziura'),
            new OA\Parameter(name: 'page',  in: 'query', schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer'), example: 10),
            new OA\Parameter(name: 'sort',  in: 'query', schema: new OA\Schema(type: 'string'), example: 'name'),
            new OA\Parameter(name: 'order', in: 'query', schema: new OA\Schema(type: 'string', enum: ['ASC','DESC']), example: 'ASC'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca data + meta',
                content: new OA\JsonContent(type: 'object')
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = ['name' => $request->query->get('name')];
        $page    = max(1, (int)$request->query->get('page', 1));
        $limit   = min(100, max(1, (int)$request->query->get('limit', 10)));
        $sort    = $request->query->get('sort', 'name');
        $order   = strtoupper($request->query->get('order', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $result = $this->tagService->listFiltered($filters, $page, $limit, $sort, $order);
        return $this->json($result, 200);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Utwórz nowy tag',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nowy tag')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tag utworzony'),
            new OA\Response(response: 400, description: 'Brak pola name')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tag  = $this->tagService->create($data);
        return $this->json($this->tagService->serialize($tag), 201);
    }

    #[Route(path: '/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz tag po ID',
        parameters: [ new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')) ],
        responses: [
            new OA\Response(response: 200, description: 'Tag znaleziony'),
            new OA\Response(response: 404, description: 'Tag nie znaleziony')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $list = $this->tagService->listFiltered([], 1, PHP_INT_MAX);
            $tag = array_values(array_filter($list['data'], fn($t)=>$t['id']===$id))[0] ?? null;
            if (!$tag) {
                return $this->json(['error'=>'Not found'], 404);
            }
            return $this->json($tag, 200);
        } catch (\Exception $e) {
            return $this->json(['error'=>'Not found'], 404);
        }
    }

    #[Route(path: '/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Aktualizuj tag',
        parameters: [ new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')) ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [ new OA\Property(property: 'name', type: 'string', example: 'Zmieniona nazwa') ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Tag zaktualizowany'),
            new OA\Response(response: 400, description: 'Błędne dane'),
            new OA\Response(response: 404, description: 'Tag nie znaleziony')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data   = json_decode($request->getContent(), true);
            $tag    = $this->tagService->update($id, $data);
            return $this->json($this->tagService->serialize($tag), 200);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }

    #[Route(path: '/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń tag',
        parameters: [ new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')) ],
        responses: [
            new OA\Response(response: 204, description: 'Tag usunięty'),
            new OA\Response(response: 404, description: 'Tag nie znaleziony')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->tagService->delete($id);
            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()], 404);
        }
    }
}
