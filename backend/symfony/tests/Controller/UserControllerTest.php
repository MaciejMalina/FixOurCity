<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private array $createdUsers = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdUsers as $email) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user) {
                $this->entityManager->remove($user);
            }
        }
        $this->entityManager->flush();
        parent::tearDown();
    }

    private function getToken(string $email = null): string
    {
        $email = $email ?? 'test_' . uniqid() . '@example.com';
        $password = 'password123';

        // Rejestracja
        $this->client->request('POST', '/api/users/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => $email,
            'password' => $password,
            'firstName' => 'Test',
            'lastName' => 'User'
        ]));

        $this->createdUsers[] = $email;

        // Logowanie
        $this->client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => $email,
            'password' => $password
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        return $data['token'];
    }

    public function testIndex(): void
    {
        $token = $this->getToken();

        $this->client->request('GET', '/api/users', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    public function testShowSuccess(): void
    {
        $token = $this->getToken();
        $email = uniqid('show_') . '@example.com';

        $user = new User();
        $user->setEmail($email)
            ->setPassword('dummy')
            ->setFirstName('Test')
            ->setLastName('Show')
            ->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->createdUsers[] = $email;

        $this->client->request('GET', '/api/users/' . $user->getId(), [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    public function testShowNotFound(): void
    {
        $token = $this->getToken();

        $this->client->request('GET', '/api/users/999999', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteSuccess(): void
    {
        $token = $this->getToken();
        $email = uniqid('delete_') . '@example.com';

        $user = new User();
        $user->setEmail($email)
            ->setPassword('dummy')
            ->setFirstName('Delete')
            ->setLastName('User')
            ->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/users/' . $user->getId(), [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testDeleteNotFound(): void
    {
        $token = $this->getToken();

        $this->client->request('DELETE', '/api/users/999999', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatusCodeSame(404);
    }
}
