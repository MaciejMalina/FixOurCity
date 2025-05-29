<?php
namespace App\Tests\Controller;

use App\Controller\ImageController;
use App\Service\ImageService;
use App\Entity\Image;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageControllerTest extends TestCase
{
    private ImageService $service;
    private ImageController $controller;

    protected function setUp(): void
    {
        $this->service    = $this->createMock(ImageService::class);
        $this->controller = new ImageController($this->service);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testListImages(): void
    {
        $expected = [
            'data' => [['id'=>1,'url'=>'u.png']],
            'meta' => ['page'=>1,'limit'=>10,'total'=>1,'pages'=>1]
        ];
        $this->service->method('listFiltered')->willReturn($expected);

        $request  = new Request(['reportId'=>'','page'=>1,'limit'=>10,'sort'=>'createdAt','order'=>'ASC']);
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    public function testCreateImageBad(): void
    {
        $this->service
             ->method('create')
             ->willThrowException(new BadRequestHttpException('Missing'));
        $request = new Request([], [], [], [], [], [], json_encode(['url'=>'']));
        $response = $this->controller->create($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateImageNotFoundReport(): void
    {
        $this->service
             ->method('create')
             ->willThrowException(new NotFoundHttpException('No rpt'));
        $request = new Request([], [], [], [], [], [], json_encode(['reportId'=>42,'url'=>'x']));
        $response = $this->controller->create($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testShowImageNotFound(): void
    {
        $this->service->method('listFiltered')->willReturn(['data'=>[], 'meta'=>[]]);
        $response = $this->controller->show(99);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testShowImage(): void
    {
        $entry = ['id'=>3,'url'=>'z.png'];
        $this->service->method('listFiltered')->willReturn(['data'=>[$entry],'meta'=>[]]);

        $response = $this->controller->show(3);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($entry, json_decode($response->getContent(), true));
    }

    public function testUpdateImageBad(): void
    {
        $this->service->method('update')->willThrowException(new BadRequestHttpException('No url'));
        $response = $this->controller->update(5, new Request([], [], [], [], [], [], json_encode([])));
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUpdateImageNotFound(): void
    {
        $this->service->method('update')->willThrowException(new NotFoundHttpException('Not found'));
        $request = new Request([], [], [], [], [], [], json_encode(['url'=>'x']));
        $response = $this->controller->update(6, $request);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteImageNotFound(): void
    {
        $this->service->method('delete')->willThrowException(new NotFoundHttpException('NF'));
        $response = $this->controller->delete(7);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteImage(): void
    {
        $this->service
             ->expects($this->once())
             ->method('delete')
             ->with(8);

        $response = $this->controller->delete(8);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
