<?php
namespace App\Tests\Controller;

use App\Controller\StatusController;
use App\Entity\Status;
use App\Service\StatusService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StatusControllerTest extends TestCase
{
    private StatusService $service;
    private StatusController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(StatusService::class);
        $this->controller = new StatusController($this->service);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testListStatuses(): void
    {
        $expected = [
            'data' => [
                ['id' => 1, 'label' => 'Nowe']
            ],
            'meta' => ['total' => 1, 'page' => 1, 'limit' => 10]
        ];
        $this->service
             ->method('listFiltered')
             ->willReturn($expected);

        $request = new Request(['label' => '', 'page' => 1, 'limit' => 10, 'sort' => 'label', 'order' => 'ASC']);
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($expected, $data);
    }

    public function testCreateStatusBad(): void
    {
        $this->service
             ->method('create')
             ->willThrowException(new BadRequestHttpException('No label'));

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->create($request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('No label', $data['error']);
    }

    public function testCreateStatus(): void
    {
        $statusObj = $this->getMockBuilder(Status::class)
                          ->disableOriginalConstructor()
                          ->onlyMethods(['getId','getLabel'])
                          ->getMock();
        $statusObj->method('getId')->willReturn(2);
        $statusObj->method('getLabel')->willReturn('Zatwierdzone');

        $this->service
             ->expects($this->once())
             ->method('create')
             ->with(['label' => 'Zatwierdzone'])
             ->willReturn($statusObj);

        $request = new Request([], [], [], [], [], [], json_encode(['label' => 'Zatwierdzone']));
        $response = $this->controller->create($request);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame(2, $data['id']);
        $this->assertSame('Zatwierdzone', $data['label']);
    }

    public function testShowStatusNotFound(): void
    {
        $this->service
             ->method('listFiltered')
             ->willReturn(['data' => [], 'meta' => []]);

        $response = $this->controller->show(99);
        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testShowStatus(): void
    {
        $entry = ['id' => 3, 'label' => 'W trakcie'];
        $this->service
             ->method('listFiltered')
             ->willReturn(['data' => [$entry], 'meta' => []]);

        $response = $this->controller->show(3);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($entry, $data);
    }

    public function testUpdateStatusBad(): void
    {
        $this->service
             ->method('update')
             ->willThrowException(new BadRequestHttpException('Err'));

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->update(4, $request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Err', $data['error']);
    }

    public function testUpdateStatusNotFound(): void
    {
        $this->service
             ->method('update')
             ->willThrowException(new NotFoundHttpException('NF'));

        $request = new Request([], [], [], [], [], [], json_encode(['label' => 'X']));
        $response = $this->controller->update(5, $request);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testDeleteStatusNotFound(): void
    {
        $this->service
             ->method('delete')
             ->willThrowException(new NotFoundHttpException('NF'));

        $response = $this->controller->delete(6);
        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('NF', $data['error']);
    }

    public function testDeleteStatus(): void
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
