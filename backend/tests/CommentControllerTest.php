<?php
namespace App\Tests\Controller;

use App\Controller\CommentController;
use App\Entity\Comment;
use App\Service\CommentService;
use App\Repository\CommentRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CommentControllerTest extends TestCase
{
    private CommentRepository $repo;
    private CommentService    $service;
    private CommentController $controller;

    protected function setUp(): void
    {
        $this->repo    = $this->createMock(CommentRepository::class);
        $this->service = $this->createMock(CommentService::class);

        $this->controller = $this->getMockBuilder(\App\Controller\CommentController::class)
            ->setConstructorArgs([$this->repo, $this->service])
            ->onlyMethods(['getUser'])
            ->getMock();

        $dummyUser = $this->createMock(\App\Entity\User::class);
        $this->controller->method('getUser')->willReturn($dummyUser);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }


    public function testListComments(): void
    {
        $cmt = $this->getMockBuilder(Comment::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $cmt->method('getId')->willReturn(1);
        $cmt->method('getContent')->willReturn('X');
        $cmt->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2020-01-01'));

        $this->repo->method('findBy')->willReturn([$cmt]);
        $this->repo->method('findAll')->willReturn([$cmt]);

        $request  = new Request(['page'=>1,'limit'=>10,'sort'=>'createdAt','order'=>'ASC']);
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        $this->assertEquals(1, $json['meta']['total']);
    }

    public function testCreateCommentBad(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode(['reportId'=>null,'content'=>'']));
        $response = $this->controller->create($request);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateComment(): void
    {
        $cmt = $this->getMockBuilder(Comment::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $cmt->method('getId')->willReturn(2);
        $cmt->method('getContent')->willReturn('Hi');
        $cmt->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2021-02-02'));

        $this->service->method('create')->willReturn($cmt);

        $request  = new Request([], [], [], [], [], [], json_encode(['reportId'=>1,'content'=>'Hi']));
        $response = $this->controller->create($request);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testShowCommentNotFound(): void
    {
        $this->repo->method('find')->willReturn(null);
        $response = $this->controller->show(99);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testShowComment(): void
    {
        $cmt = $this->getMockBuilder(Comment::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $cmt->method('getId')->willReturn(3);
        $cmt->method('getContent')->willReturn('C');
        $cmt->method('getCreatedAt')->willReturn(new \DateTimeImmutable);

        $this->repo->method('find')->willReturn($cmt);

        $response = $this->controller->show(3);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateCommentNotFound(): void
    {
        $this->repo->method('find')->willReturn(null);
        $response = $this->controller->update(4, new Request([], [], [], [], [], [], json_encode(['content'=>'X'])));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdateCommentNoData(): void
    {
        $cmt = $this->getMockBuilder(Comment::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $this->repo->method('find')->willReturn($cmt);

        $response = $this->controller->update(5, new Request([], [], [], [], [], [], json_encode([])));
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUpdateComment(): void
    {
        $cmt = $this->getMockBuilder(Comment::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $this->repo->method('find')->willReturn($cmt);
        $this->service
             ->expects($this->once())
             ->method('update')
             ->with($cmt, ['content'=>'Z'])
             ->willReturn($cmt);

        $response = $this->controller->update(6, new Request([], [], [], [], [], [], json_encode(['content'=>'Z'])));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteCommentNotFound(): void
    {
        $this->repo->method('find')->willReturn(null);
        $response = $this->controller->delete(7);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteComment(): void
    {
        $cmt = $this->getMockBuilder(Comment::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $this->repo->method('find')->willReturn($cmt);
        $this->service
             ->expects($this->once())
             ->method('delete')
             ->with($cmt);

        $response = $this->controller->delete(8);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
