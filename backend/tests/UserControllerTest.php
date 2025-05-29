<?php
namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Service\UserService;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UserControllerTest extends TestCase
{
    private UserService $userService;
    private UserRepository $repo;
    private UserController $controller;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->repo        = $this->createMock(UserRepository::class);
        $this->controller  = new UserController($this->userService, $this->repo);

        $c = $this->createMock(ContainerInterface::class);
        $c->method('has')->willReturn(false);
        $this->controller->setContainer($c);
    }

    private function makeUser(int $id, string $email, string $fn, string $ln, array $roles): User
    {
        $u = $this->getMockBuilder(User::class)
                  ->disableOriginalConstructor()
                  ->getMock();
        $u->method('getId')->willReturn($id);
        $u->method('getEmail')->willReturn($email);
        $u->method('getFirstName')->willReturn($fn);
        $u->method('getLastName')->willReturn($ln);
        $u->method('getRoles')->willReturn($roles);
        return $u;
    }

    public function testListUsers(): void
    {
        $u1 = $this->makeUser(1,'a@b','A','B',['ROLE_USER']);
        $u2 = $this->makeUser(2,'c@d','C','D',['ROLE_ADMIN']);

        $paginator = $this->createMock(\Doctrine\ORM\Tools\Pagination\Paginator::class);
        $paginator->method('count')->willReturn(2);
        $paginator->method('getIterator')
                ->willReturn(new \ArrayIterator([$u1, $u2]));

        $this->userService
            ->method('list')
            ->willReturn($paginator);

        $request = new Request();

        set_error_handler(fn() => true, E_WARNING);
        $response = $this->controller->list($request);
        restore_error_handler();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $json = json_decode($response->getContent(), true);
        $this->assertCount(2, $json['data']);
        $this->assertEquals(2, $json['meta']['total']);
    }


    public function testUpdateUser(): void
    {
        $existing = $this->makeUser(9,'old@o','Old','O',['ROLE_USER']);
        $updated  = $this->makeUser(9,'old@o','New','Name',['ROLE_USER']);
        $payload  = ['firstName'=>'New','lastName'=>'Name'];

        $this->userService
             ->expects($this->once())
             ->method('update')
             ->with($existing, $payload)
             ->willReturn($updated);

        $request = new Request([], [], [], [], [], [], json_encode($payload));
        $response = $this->controller->update($existing, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        $this->assertEquals('New', $json['firstName']);
    }

    public function testDeleteUser(): void
    {
        $user = $this->makeUser(11,'','','',[]);
        $this->userService
            ->expects($this->once())
            ->method('delete')
            ->with($user);

        $response = $this->controller->delete($user);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertSame('{}', $response->getContent());
    }
}
