<?php
namespace App\Tests\Controller;

use App\Controller\AuthController;
use App\Entity\User;
use App\Service\AuthService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AuthControllerTest extends TestCase
{
    private AuthService $service;
    private AuthController $controller;

    protected function setUp(): void
    {
        $this->service    = $this->createMock(AuthService::class);
        $this->controller = new AuthController($this->service);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    public function testRegister(): void
    {
        $user = $this->getMockBuilder(User::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $user->method('getId')->willReturn(1);
        $user->method('getEmail')->willReturn('a@b');

        $this->service
             ->expects($this->once())
             ->method('register')
             ->with([
                 'email'=>'a@b','password'=>'p','firstName'=>'F','lastName'=>'L'
             ])
             ->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode([
            'email'=>'a@b','password'=>'p','firstName'=>'F','lastName'=>'L'
        ]));
        $response = $this->controller->register($request);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testLogin(): void
    {
        $resp = new JsonResponse(['ok'=>true], 200);
        $this->service->method('login')->willReturn($resp);

        $request  = new Request([], [], [], [], [], [], json_encode(['email'=>'a','password'=>'b']));
        $response = $this->controller->login($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRefresh(): void
    {
        $resp = new JsonResponse([], 200);
        $this->service->method('refresh')->willReturn($resp);

        $response = $this->controller->refresh(new Request());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLogout(): void
    {
        $resp = new JsonResponse(null, 204);
        $this->service->method('logout')->willReturn($resp);

        $this->controller = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->service])
            ->onlyMethods(['getUser'])
            ->getMock();
        $this->controller->method('getUser')->willReturn(null);

        $response = $this->controller->logout(new Request());
        $this->assertEquals(204, $response->getStatusCode());
    }
}
