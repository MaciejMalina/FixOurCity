<?php
namespace App\Tests\Controller;

use App\Controller\StatusController;
use App\Entity\Status;
use App\Service\StatusService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StatusControllerTest extends TestCase
{
    private StatusService $service;
    private StatusController $controller;

    protected function setUp(): void
    {
        $this->service    = $this->createMock(StatusService::class);
        $this->controller = new StatusController($this->service);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testListStatuses(): void
    {
        $expected = [
            'data' => [['id'=>1,'label'=>'Nowe']],
            'meta' => ['page'=>1,'limit'=>10,'total'=>1,'pages'=>1]
        ];
        $this->service
             ->method('listFiltered')
             ->willReturn($expected);

        $request  = new Request(['label'=>'','page'=>1,'limit'=>10,'sort'=>'label','order'=>'ASC']);
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    public function testCreateStatus(): void
    {
        $payload = ['label'=>'Test'];
        $status = $this->getMockBuilder(Status::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $status->method('getId')->willReturn(2);
        $status->method('getLabel')->willReturn('Test');

        $this->service
             ->expects($this->once())
             ->method('create')
             ->with($payload)
             ->willReturn($status);

        $request  = new Request([], [], [], [], [], [], json_encode($payload));
        $response = $this->controller->create($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(['id'=>2,'label'=>'Test'], json_decode($response->getContent(), true));
    }

    public function testShowStatusNotFound(): void
    {
        $this->service->method('listFiltered')->willReturn(['data'=>[], 'meta'=>[]]);

        $response = $this->controller->show(99);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testShowStatusFound(): void
    {
        $entry = ['id'=>5,'label'=>'Foo'];
        $this->service->method('listFiltered')->willReturn(['data'=>[$entry],'meta'=>[]]);

        $response = $this->controller->show(5);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($entry, json_decode($response->getContent(), true));
    }

    public function testUpdateStatus(): void
    {
        $payload = ['label'=>'Zakt'];
        $status = $this->getMockBuilder(Status::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $status->method('getId')->willReturn(7);
        $status->method('getLabel')->willReturn('Zakt');

        $this->service
             ->expects($this->once())
             ->method('update')
             ->with(7, $payload)
             ->willReturn($status);

        $request  = new Request([], [], [], [], [], [], json_encode($payload));
        $response = $this->controller->update(7, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['id'=>7,'label'=>'Zakt'], json_decode($response->getContent(), true));
    }

    public function testDeleteStatus(): void
    {
        $this->service
             ->expects($this->once())
             ->method('delete')
             ->with(8);

        $response = $this->controller->delete(8);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
