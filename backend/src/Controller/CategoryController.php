<?php

namespace App\Controller;

use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[OA\Tag(name: 'Categories')]
#[Route(path: '/api/v1/categories')]
class CategoryController extends AbstractController
{
    public function __construct(private CategoryService $categoryService) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista kategorii (paginacja, filtrowanie, sortowanie)',
        parameters: [
            new OA\Parameter(name:'name', in:'query', schema:new OA\Schema(type:'string'), example:'Oświetlenie'),
            new OA\Parameter(name:'page', in:'query', schema:new OA\Schema(type:'integer'), example:1),
            new OA\Parameter(name:'limit', in:'query', schema:new OA\Schema(type:'integer'), example:10),
            new OA\Parameter(name:'sort', in:'query', schema:new OA\Schema(type:'string'), example:'name'),
            new OA\Parameter(name:'order', in:'query', schema:new OA\Schema(type:'string', enum:['ASC','DESC']), example:'ASC'),
        ],
        responses: [
            new OA\Response(response:200, description:'Zwraca data + meta')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = ['name'=>$request->query->get('name')];
        $page    = max(1,(int)$request->query->get('page',1));
        $limit   = min(100,max(1,(int)$request->query->get('limit',10)));
        $sort    = $request->query->get('sort','name');
        $order   = strtoupper($request->query->get('order','ASC'))==='DESC'?'DESC':'ASC';

        return $this->json(
            $this->categoryService->listFiltered($filters,$page,$limit,$sort,$order),
            200
        );
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Utwórz kategorię',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required:['name'],
                properties:[ new OA\Property(property:'name',type:'string',example:'Nowa kategoria') ]
            )
        ),
        responses:[
            new OA\Response(response:201,description:'Kategoria utworzona'),
            new OA\Response(response:400,description:'Brak nazwy')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(),true);
            $c    = $this->categoryService->create($data);
            return $this->json(['id'=>$c->getId(),'name'=>$c->getName()],201);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error'=>$e->getMessage()],400);
        }
    }

    #[Route(path:'/{id}', methods:['GET'])]
    #[OA\Get(
        summary:'Pobierz kategorię',
        parameters:[new OA\Parameter(name:'id',in:'path',required:true,schema:new OA\Schema(type:'integer'))],
        responses:[
            new OA\Response(response:200,description:'Znaleziono'),
            new OA\Response(response:404,description:'Nie znaleziono')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $all    = $this->categoryService->listFiltered([],1,PHP_INT_MAX);
            $found  = array_filter($all['data'], fn($c)=>$c['id']===$id);
            if (empty($found)) {
                return $this->json(['error'=>'Not found'],404);
            }
            return $this->json(array_values($found)[0],200);
        } catch (\Throwable) {
            return $this->json(['error'=>'Not found'],404);
        }
    }

    #[Route(path:'/{id}', methods:['PATCH'])]
    #[OA\Patch(
        summary:'Aktualizuj kategorię',
        parameters:[new OA\Parameter(name:'id',in:'path',required:true,schema:new OA\Schema(type:'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required:['name'],
                properties:[ new OA\Property(property:'name',type:'string',example:'Zmieniona kategoria') ]
            )
        ),
        responses:[
            new OA\Response(response:200,description:'Zaktualizowano'),
            new OA\Response(response:400,description:'Błędne dane'),
            new OA\Response(response:404,description:'Nie znaleziono')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data   = json_decode($request->getContent(),true);
            $cat    = $this->categoryService->update($id,$data);
            return $this->json(['id'=>$cat->getId(),'name'=>$cat->getName()],200);
        } catch (BadRequestHttpException $e) {
            return $this->json(['error'=>$e->getMessage()],400);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()],404);
        }
    }

    #[Route(path:'/{id}', methods:['DELETE'])]
    #[OA\Delete(
        summary:'Usuń kategorię',
        parameters:[new OA\Parameter(name:'id',in:'path',required:true,schema:new OA\Schema(type:'integer'))],
        responses:[
            new OA\Response(response:204,description:'Usunięto'),
            new OA\Response(response:404,description:'Nie znaleziono')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->categoryService->delete($id);
            return $this->json(null,204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error'=>$e->getMessage()],404);
        }
    }
}
