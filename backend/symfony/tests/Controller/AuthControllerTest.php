<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class AuthControllerTest extends WebTestCase
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

    public function testRegisterSuccess()
    {
        $email = 'test_' . uniqid() . '@example.com';
        $this->createdUsers[] = $email;

        $this->client->request('POST', '/api/users/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => $email,
            'password' => 'password123',
            'firstName' => 'John',
            'lastName' => 'Doe'
        ]));

        $this->assertResponseStatusCodeSame(201);
    }

    public function testRegisterMissingFields()
    {
        $this->client->request('POST', '/api/users/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test_' . uniqid() . '@example.com'
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLoginSuccess()
    {
        $email = 'testlogin_' . uniqid() . '@example.com';
        $this->createdUsers[] = $email;

        $this->client->request('POST', '/api/users/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => $email,
            'password' => 'password123',
            'firstName' => 'Jane',
            'lastName' => 'Doe'
        ]));

        $this->client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => $email,
            'password' => 'password123'
        ]));

        $this->assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $content);
    }

    public function testLoginInvalidCredentials()
    {
        $this->client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'nonexistent_' . uniqid() . '@example.com',
            'password' => 'wrongpassword'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }
}
