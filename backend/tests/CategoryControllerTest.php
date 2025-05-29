<?php
namespace App\Tests\Controller;

use App\Controller\CategoryController;
use App\Entity\Category;
use App\Service\CategoryService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CategoryControllerTest extends TestCase
{
    private CategoryService $service;
    private CategoryController $controller;

    protected function setUp(): void
    {
        $this->service    = $this->createMock(CategoryService::class);
        $this->controller = new CategoryController($this->service);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testListCategories(): void
    {
        $expected = [
            'data' => [['id'=>1,'name'=>'X']],
            'meta' => ['page'=>1,'limit'=>10,'total'=>1,'pages'=>1]
        ];
        $this->service->method('listFiltered')->willReturn($expected);

        $request  = new Request(['name'=>'','page'=>1,'limit'=>10,'sort'=>'name','order'=>'ASC']);
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateCategoryBad(): void
    {
        $this->service
             ->method('create')
             ->willThrowException(new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('No name'));

        $response = $this->controller->create(new Request([], [], [], [], [], [], json_encode([])));
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateCategory(): void
    {
        $cat = $this->getMockBuilder(Category::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $cat->method('getId')->willReturn(2);
        $cat->method('getName')->willReturn('Y');

        $this->service
             ->expects($this->once())
             ->method('create')
             ->with(['name'=>'Y'])
             ->willReturn($cat);

        $response = $this->controller->create(new Request([], [], [], [], [], [], json_encode(['name'=>'Y'])));
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testShowCategoryNotFound(): void
    {
        $this->service->method('listFiltered')->willReturn(['data'=>[], 'meta'=>[]]);
        $response = $this->controller->show(99);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testShowCategory(): void
    {
        $entry = ['id'=>3,'name'=>'Z'];
        $this->service->method('listFiltered')->willReturn(['data'=>[$entry],'meta'=>[]]);
        $response = $this->controller->show(3);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateCategoryBad(): void
    {
        $this->service
             ->method('update')
             ->willThrowException(new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Err'));

        $response = $this->controller->update(4, new Request([], [], [], [], [], [], json_encode([])));
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUpdateCategoryNotFound(): void
    {
        $this->service
             ->method('update')
             ->willThrowException(new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('NF'));

        $response = $this->controller->update(5, new Request([], [], [], [], [], [], json_encode(['name'=>'A'])));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteCategoryNotFound(): void
    {
        $this->service
             ->method('delete')
             ->willThrowException(new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('NF'));

        $response = $this->controller->delete(6);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteCategory(): void
    {
        $this->service
             ->expects($this->once())
             ->method('delete')
             ->with(7);

        $response = $this->controller->delete(7);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
