<?php
namespace App\Tests\Controller;

use App\Controller\CategoryController;
use App\Entity\Category;
use App\Service\CategoryService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryControllerTest extends TestCase
{
    private CategoryService $service;
    private CategoryController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(CategoryService::class);
        $this->controller = new CategoryController($this->service);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testListCategories(): void
    {
        $expected = [
            'data' => [
                ['id' => 1, 'name' => 'X']
            ],
            'meta' => ['total' => 1, 'page' => 1, 'limit' => 10]
        ];
        $this->service
             ->method('listFiltered')
             ->willReturn($expected);

        $request = new Request();
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($expected, $data);
    }

    public function testCreateCategoryBad(): void
    {
        $this->service
             ->method('create')
             ->willThrowException(new BadRequestHttpException('No name'));

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->create($request);

        $this->assertEquals(400, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertStringContainsString('No name', $content['error']);
    }

    public function testCreateCategory(): void
    {
        $cat = $this->getMockBuilder(Category::class)
                    ->getMock();
        $cat->method('getId')->willReturn(2);
        $cat->method('getName')->willReturn('Y');

        $this->service
             ->method('create')
             ->willReturn($cat);
        $this->service
             ->method('serialize')
             ->willReturn(['id'=>2,'name'=>'Y']);

        $request = new Request([], [], [], [], [], [], json_encode(['name' => 'Y']));
        $response = $this->controller->create($request);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame(2, $data['id']);
        $this->assertArrayHasKey('name', $data);
        $this->assertSame('Y', $data['name']);
    }

    public function testShowCategoryNotFound(): void
    {
        $this->service
             ->method('listFiltered')
             ->willReturn(['data'=>[], 'meta'=>[]]);

        $response = $this->controller->show(99);
        $this->assertEquals(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
    }

    public function testShowCategory(): void
    {
        $entry = ['id' => 3, 'name' => 'Z'];
        $this->service
             ->method('listFiltered')
             ->willReturn(['data'=>[$entry], 'meta'=>[]]);

        $response = $this->controller->show(3);
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame($entry, $content);
    }

    public function testUpdateCategoryBad(): void
    {
        $this->service
             ->method('update')
             ->willThrowException(new BadRequestHttpException('Err'));

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->update(4, $request);

        $this->assertEquals(400, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertStringContainsString('Err', $content['error']);
    }

    public function testUpdateCategoryNotFound(): void
    {
        $this->service
             ->method('update')
             ->willThrowException(new NotFoundHttpException('NF'));

        $request = new Request([], [], [], [], [], [], json_encode(['name' => 'A']));
        $response = $this->controller->update(5, $request);

        $this->assertEquals(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertStringContainsString('NF', $content['error']);
    }

    public function testDeleteCategoryNotFound(): void
    {
        $this->service
             ->method('delete')
             ->willThrowException(new NotFoundHttpException('NF'));

        $response = $this->controller->delete(6);
        $this->assertEquals(404, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertStringContainsString('NF', $content['error']);
    }

    public function testDeleteCategory(): void
    {
        $this->service
             ->expects($this->once())
             ->method('delete')
             ->with(7);

        $response = $this->controller->delete(7);
        $this->assertEquals(204, $response->getStatusCode());
        $body = $response->getContent();
        $this->assertTrue(
            $body === '' || $body === 'null' || $body === '{}',
        );
    }
}
