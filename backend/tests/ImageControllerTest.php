<?php
namespace App\Tests\Controller;

use App\Controller\ImageController;
use App\Entity\Image;
use App\Service\ImageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ImageControllerTest extends TestCase
{
    private ImageService $imageService;
    private ImageController $controller;

    protected function setUp(): void
    {
        $this->imageService = $this->createMock(ImageService::class);
        $this->controller = new ImageController($this->imageService);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testIndexReturnsPaginatedList(): void
    {
        $expected = [
            'data' => [],
            'meta' => ['total' => 0, 'page' => 1, 'limit' => 10, 'pages' => 1]
        ];
        $this->imageService
             ->method('listFiltered')
             ->willReturn($expected);

        $request = new Request();
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($expected, $data);
    }

    public function testCreateImageMissingFields(): void
    {
        $this->imageService
             ->method('create')
             ->willThrowException(new BadRequestHttpException('reportId and url are required'));

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->create($request);
        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testCreateImageInvalidReport(): void
    {
        $this->imageService
             ->method('create')
             ->willThrowException(new NotFoundHttpException('Report not found'));

        $body = ['reportId' => 999, 'url' => 'http://foo/x.png'];
        $request = new Request([], [], [], [], [], [], json_encode($body));
        $response = $this->controller->create($request);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Report not found', $data['error']);
    }

    public function testCreateImageSuccess(): void
    {
        $imgObj = $this->getMockBuilder(Image::class)
                       ->getMock();
        $imgObj->method('getId')->willReturn(20);
        $imgObj->method('getUrl')->willReturn('http://foo/y.png');
        $imgObj->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2025-06-05T12:00:00+00:00'));

        $reportMock = $this->getMockBuilder(\App\Entity\Report::class)->getMock();
        $reportMock->method('getId')->willReturn(8);
        $imgObj->method('getReport')->willReturn($reportMock);

        $this->imageService
             ->method('create')
             ->willReturn($imgObj);
        $this->imageService
             ->method('serialize')
             ->willReturn([
                 'id'=>20,'url'=>'http://foo/y.png','createdAt'=>'2025-06-05T12:00:00+00:00','reportId'=>8
             ]);

        $body = ['reportId' => 8, 'url' => 'http://foo/y.png'];
        $request = new Request([], [], [], [], [], [], json_encode($body));
        $response = $this->controller->create($request);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame(20, $data['id']);
        $this->assertSame('http://foo/y.png', $data['url']);
    }

    public function testShowImageNotFound(): void
    {
        $this->imageService
             ->method('listFiltered')
             ->willReturn(['data'=>[], 'meta'=>[]]);

        $response = $this->controller->show(123);
        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testShowImageFound(): void
    {
        $entry = ['id'=>5,'url'=>'http://foo/5.png','createdAt'=>'2025-06-05T12:00:00+00:00','reportId'=>2];
        $this->imageService
             ->method('listFiltered')
             ->willReturn(['data'=>[$entry], 'meta'=>[]]);

        $response = $this->controller->show(5);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($entry, $data);
    }

    public function testDeleteImageNotFound(): void
    {
        $this->imageService
             ->method('delete')
             ->willThrowException(new NotFoundHttpException('NF'));

        $response = $this->controller->delete(6);
        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('NF', $data['error']);
    }

    public function testDeleteImage(): void
    {
        $this->imageService
             ->expects($this->once())
             ->method('delete')
             ->with(7);

        $response = $this->controller->delete(7);
        $this->assertEquals(204, $response->getStatusCode());
        $body = $response->getContent();
        $this->assertTrue(
            $body === '' || $body === 'null' || $body === '{}'
        );
    }
}
