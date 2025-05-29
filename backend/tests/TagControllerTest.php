<?php
namespace App\Tests\Controller;

use App\Controller\TagController;
use App\Entity\Tag;
use App\Service\TagService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TagControllerTest extends TestCase
{
    private TagService $service;
    private TagController $controller;

    protected function setUp(): void
    {
        $this->service    = $this->createMock(TagService::class);
        $this->controller = new TagController($this->service);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testListTags(): void
    {
        $expected = [
            'data' => [['id'=>1,'name'=>'foo']],
            'meta' => ['page'=>1,'limit'=>10,'total'=>1,'pages'=>1]
        ];
        $this->service
             ->method('listFiltered')
             ->willReturn($expected);

        $request  = new Request(['name'=>'','page'=>1,'limit'=>10,'sort'=>'name','order'=>'ASC']);
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    public function testCreateTag(): void
    {
        $payload = ['name'=>'bar'];
        $tag = $this->getMockBuilder(Tag::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $tag->method('getId')->willReturn(5);
        $tag->method('getName')->willReturn('bar');

        $this->service
             ->expects($this->once())
             ->method('create')
             ->with($payload)
             ->willReturn($tag);
        $this->service
             ->method('serialize')
             ->willReturn(['id'=>5,'name'=>'bar']);

        $request  = new Request([], [], [], [], [], [], json_encode($payload));
        $response = $this->controller->create($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(['id'=>5,'name'=>'bar'], json_decode($response->getContent(), true));
    }

    public function testShowTagNotFound(): void
    {
        $this->service
             ->method('listFiltered')
             ->willReturn(['data'=>[], 'meta'=>[]]);

        $response = $this->controller->show(99);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdateTag(): void
    {
        $payload = ['name'=>'xyz'];
        $tag = $this->getMockBuilder(Tag::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $tag->method('getId')->willReturn(7);
        $tag->method('getName')->willReturn('xyz');

        $this->service
             ->expects($this->once())
             ->method('update')
             ->with(7, $payload)
             ->willReturn($tag);
        $this->service
             ->method('serialize')
             ->willReturn(['id'=>7,'name'=>'xyz']);

        $request  = new Request([], [], [], [], [], [], json_encode($payload));
        $response = $this->controller->update(7, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['id'=>7,'name'=>'xyz'], json_decode($response->getContent(), true));
    }

    public function testDeleteTag(): void
    {
        $this->service
             ->expects($this->once())
             ->method('delete')
             ->with(13);

        $response = $this->controller->delete(13);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
