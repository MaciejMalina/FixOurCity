<?php
namespace App\Tests\Controller;

use App\Controller\CommentController;
use App\Entity\Comment;
use App\Service\CommentService;
use App\Repository\CommentRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentControllerTest extends TestCase
{
    private CommentService $commentService;
    private CommentRepository $commentRepo;
    private CommentController $controller;

    protected function setUp(): void
    {
        $this->commentService = $this->createMock(CommentService::class);
        $this->commentRepo    = $this->createMock(CommentRepository::class);
        $this->controller     = new CommentController($this->commentService, $this->commentRepo);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testIndexReturnsPaginatedListWithoutFilter(): void
    {
        $expected = [
            'data' => [],
            'meta' => ['total' => 0, 'page' => 1, 'limit' => 10, 'pages' => 1]
        ];
        $this->commentService
             ->method('listFiltered')
             ->willReturn($expected);

        $request = new Request();
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($expected, $data);
    }

    public function testCreateCommentMissingFields(): void
    {
        $this->commentService
             ->method('create')
             ->willThrowException(new BadRequestHttpException('Missing fields'));

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->create($request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Missing fields', $data['error']);
    }

    public function testCreateCommentInvalidReport(): void
    {
        $this->commentService
             ->method('create')
             ->willThrowException(new NotFoundHttpException('Report not found'));

        $body = ['reportId' => 123, 'content' => 'Hi', 'author' => 'A'];
        $request = new Request([], [], [], [], [], [], json_encode($body));
        $response = $this->controller->create($request);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Report not found', $data['error']);
    }

    public function testCreateCommentSuccess(): void
    {
        $commentObj = $this->getMockBuilder(Comment::class)
                           ->getMock();
        $commentObj->method('getId')->willReturn(7);
        $commentObj->method('getContent')->willReturn('Test OK');
        $commentObj->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2025-06-01T12:00:00+00:00'));
        $commentObj->method('getAuthor')->willReturn('A');

        $this->commentService
             ->method('create')
             ->willReturn($commentObj);
        $this->commentService
             ->method('serialize')
             ->willReturn([
                 'id'=>7,'content'=>'Test OK','author'=>'A','createdAt'=>'2025-06-01T12:00:00+00:00','reportId'=>1
             ]);

        $body = ['reportId' => 9, 'content' => 'Okay', 'author' => 'A'];
        $request = new Request([], [], [], [], [], [], json_encode($body));
        $response = $this->controller->create($request);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertSame(7, $data['id']);
        $this->assertSame('Test OK', $data['content']);
        $this->assertArrayHasKey('createdAt', $data);
    }

    public function testShowCommentNotFound(): void
    {
        $this->commentRepo
             ->method('find')
             ->willReturn(null);

        $response = $this->controller->show(123);
        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testShowCommentFound(): void
    {
        $commentObj = $this->getMockBuilder(Comment::class)
                           ->getMock();
        $this->commentRepo
             ->method('find')
             ->willReturn($commentObj);
        $this->commentService
             ->method('serialize')
             ->willReturn(['id'=>1,'content'=>'C','author'=>'A','createdAt'=>'2025-06-01T12:00:00+00:00','reportId'=>1]);

        $response = $this->controller->show(1);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame(1, $data['id']);
    }

    public function testUpdateCommentMissingContent(): void
    {
        $commentObj = $this->getMockBuilder(Comment::class)
                           ->getMock();
        $this->commentRepo
             ->method('find')
             ->willReturn($commentObj);

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $response = $this->controller->update(1, $request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testUpdateCommentNotFound(): void
    {
        $this->commentRepo
             ->method('find')
             ->willReturn(null);

        $request = new Request([], [], [], [], [], [], json_encode(['content'=>'X']));
        $response = $this->controller->update(2, $request);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testUpdateCommentSuccess(): void
    {
        $commentObj = $this->getMockBuilder(Comment::class)
                           ->getMock();
        $this->commentRepo
             ->method('find')
             ->willReturn($commentObj);
        $this->commentService
             ->method('update')
             ->willReturn($commentObj);
        $this->commentService
             ->method('serialize')
             ->willReturn(['id'=>1,'content'=>'X','author'=>'A','createdAt'=>'2025-06-01T12:00:00+00:00','reportId'=>1]);

        $request = new Request([], [], [], [], [], [], json_encode(['content'=>'X']));
        $response = $this->controller->update(1, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('X', $data['content']);
    }

    public function testDeleteCommentNotFound(): void
    {
        $this->commentRepo
             ->method('find')
             ->willReturn(null);

        $response = $this->controller->delete(3);
        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testDeleteComment(): void
    {
        $commentObj = $this->getMockBuilder(Comment::class)
                           ->getMock();
        $this->commentRepo
             ->method('find')
             ->willReturn($commentObj);
        $this->commentService
             ->expects($this->once())
             ->method('delete')
             ->with($commentObj);

        $response = $this->controller->delete(4);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertTrue(
            $response->getContent() === '' || $response->getContent() === 'null' || $response->getContent() === '{}'
        );
    }
}
