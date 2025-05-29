<?php
namespace App\Tests\Controller;

use App\Controller\ReportController;
use App\Service\ReportService;
use App\Repository\ReportRepository;
use App\Entity\Report;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReportControllerTest extends TestCase
{
    private ReportService $service;
    private ReportRepository $repo;
    private ReportController $controller;

    protected function setUp(): void
    {
        $this->service    = $this->createMock(ReportService::class);
        $this->repo       = $this->createMock(ReportRepository::class);
        $this->controller = new ReportController($this->service, $this->repo);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testListReports(): void
    {
        $entity = $this->getMockBuilder(Report::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $this->repo->method('findFiltered')->willReturn([$entity]);
        $this->repo->method('countFiltered')->willReturn(1);
        $this->service->method('serialize')->willReturn(['id'=>1,'title'=>'T']);

        $request  = new Request([
            'page'=>1,'limit'=>10,
            'status'=>'','category'=>'',
            'sort'=>'createdAt','order'=>'ASC'
        ]);
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        $this->assertCount(1, $json['data']);
        $this->assertEquals(1, $json['meta']['total']);
    }

    public function testCreateReportBad(): void
    {
        $payload = ['title'=>'','description'=>''];
        $request = new Request([], [], [], [], [], [], json_encode($payload));
        $response = $this->controller->create($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateReport(): void
    {
        $entity = $this->getMockBuilder(Report::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $this->service
             ->expects($this->once())
             ->method('createReport')
             ->with('T','D')
             ->willReturn($entity);
        $this->service
             ->method('serialize')
             ->willReturn(['id'=>2,'title'=>'T']);

        $payload = ['title'=>'T','description'=>'D'];
        $request = new Request([], [], [], [], [], [], json_encode($payload));
        $response = $this->controller->create($request);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testShowReportNotFound(): void
    {
        $this->repo->method('find')->willReturn(null);

        $response = $this->controller->show(99);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testShowReport(): void
    {
        $entity = $this->getMockBuilder(Report::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $this->repo->method('find')->willReturn($entity);
        $this->service->method('serialize')->willReturn(['id'=>3,'title'=>'X']);

        $response = $this->controller->show(3);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateReportNotFound(): void
    {
        $this->repo->method('find')->willReturn(null);

        $response = $this->controller->update(4, new Request([], [], [], [], [], [], json_encode(['title'=>'A'])));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdateReportNoData(): void
    {
        $entity = $this->getMockBuilder(Report::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $this->repo->method('find')->willReturn($entity);

        $response = $this->controller->update(5, new Request([], [], [], [], [], [], json_encode([])));
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUpdateReport(): void
    {
        $entity = $this->getMockBuilder(Report::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $this->repo->method('find')->willReturn($entity);
        $this->service
             ->expects($this->once())
             ->method('updateReport')
             ->with($entity, ['title'=>'Z'])
             ->willReturn($entity);
        $this->service
             ->method('serialize')
             ->willReturn(['id'=>6,'title'=>'Z']);

        $response = $this->controller->update(6, new Request([], [], [], [], [], [], json_encode(['title'=>'Z'])));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteReportNotFound(): void
    {
        $this->repo->method('find')->willReturn(null);

        $response = $this->controller->delete(7);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteReport(): void
    {
        $entity = $this->getMockBuilder(Report::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $this->repo->method('find')->willReturn($entity);
        $this->service
             ->expects($this->once())
             ->method('deleteReport')
             ->with($entity);

        $response = $this->controller->delete(8);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
