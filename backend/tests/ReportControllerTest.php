<?php
namespace App\Tests\Controller;

use App\Controller\ReportController;
use App\Entity\Report;
use App\Service\ReportService;
use App\Repository\ReportRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportControllerTest extends TestCase
{
    private ReportService $service;
    private ReportRepository $repo;
    private ReportController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(ReportService::class);
        $this->repo    = $this->createMock(ReportRepository::class);
        $this->controller = new ReportController($this->service, $this->repo);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testListReports(): void
    {
        $expected = [
            'data' => [],
            'meta' => ['total' => 0, 'page' => 1, 'limit' => 10, 'pages' => 1]
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

    public function testCreateReportMissingFields(): void
    {
        $this->service
             ->method('create')
             ->willThrowException(new BadRequestHttpException('Missing fields'));

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->create($request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Missing fields', $data['error']);
    }

    public function testCreateReportSuccess(): void
    {
        $reportObj = $this->getMockBuilder(Report::class)
                          ->getMock();
        $reportObj->method('getId')->willReturn(99);
        $reportObj->method('getTitle')->willReturn('Nowy tytuł');
        $reportObj->method('getDescription')->willReturn('Nowy opis');
        $reportObj->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2025-06-02T12:00:00+00:00'));
        $statusMock = $this->getMockBuilder(\App\Entity\Status::class)->getMock();
        $categoryMock = $this->getMockBuilder(\App\Entity\Category::class)->getMock();
        $reportObj->method('getStatus')->willReturn($statusMock);
        $reportObj->method('getCategory')->willReturn($categoryMock);

        $this->service
             ->method('create')
             ->willReturn($reportObj);
        $this->service
             ->method('serialize')
             ->willReturn(['id'=>99,'title'=>'Nowy tytuł','description'=>'Nowy opis']);

        $body = ['title'=>'Nowy tytuł','description'=>'Nowy opis','categoryId'=>1,'statusId'=>1];
        $request = new Request([], [], [], [], [], [], json_encode($body));
        $response = $this->controller->create($request);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame(['id'=>99,'title'=>'Nowy tytuł','description'=>'Nowy opis'], $data);
    }

    public function testShowReportNotFound(): void
    {
        $this->repo
             ->method('find')
             ->willReturn(null);

        $response = $this->controller->show(123);
        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testShowReportFound(): void
    {
        $reportObj = $this->getMockBuilder(Report::class)
                          ->getMock();
        $this->repo
             ->method('find')
             ->willReturn($reportObj);
        $this->service
             ->method('serialize')
             ->willReturn(['id'=>124,'title'=>'T','description'=>'D']);

        $response = $this->controller->show(124);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame(['id'=>124,'title'=>'T','description'=>'D'], $data);
    }

    public function testUpdateReportNotFound(): void
    {
        $this->repo
             ->method('find')
             ->willReturn(null);

        $request = new Request([], [], [], [], [], [], json_encode(['title'=>'X']));
        $response = $this->controller->update(200, $request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdateReportNoData(): void
    {
        $reportObj = $this->getMockBuilder(Report::class)
                          ->getMock();
        $this->repo
             ->method('find')
             ->willReturn($reportObj);

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->update(201, $request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testUpdateReportSuccess(): void
    {
        $reportObj = $this->getMockBuilder(Report::class)
                          ->getMock();
        $this->repo
             ->method('find')
             ->willReturn($reportObj);
        $this->service
             ->method('update')
             ->willReturn($reportObj);
        $this->service
             ->method('serialize')
             ->willReturn(['id'=>202,'title'=>'Y','description'=>'Z']);

        $request = new Request([], [], [], [], [], [], json_encode(['title'=>'Y','description'=>'Z']));
        $response = $this->controller->update(202, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame(['id'=>202,'title'=>'Y','description'=>'Z'], $data);
    }

    public function testDeleteReportNotFound(): void
    {
        $this->repo
             ->method('find')
             ->willReturn(null);

        $response = $this->controller->delete(203);
        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testDeleteReportSuccess(): void
    {
        $reportObj = $this->getMockBuilder(Report::class)
                          ->getMock();
        $this->repo
             ->method('find')
             ->willReturn($reportObj);
        $this->service
             ->expects($this->once())
             ->method('delete')
             ->with($reportObj);

        $response = $this->controller->delete(204);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertTrue(
            $response->getContent() === '' || $response->getContent() === 'null' || $response->getContent() === '{}'
        );
    }
}
