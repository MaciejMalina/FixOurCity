<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRegisterSuccess()
    {
        $this->client->request('POST', '/api/users/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com',
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
            'email' => 'test2@example.com'
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLoginSuccess()
    {
        $this->client->request('POST', '/api/users/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'testlogin@example.com',
            'password' => 'password123',
            'firstName' => 'Jane',
            'lastName' => 'Doe'
        ]));

        $this->client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'testlogin@example.com',
            'password' => 'password123'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['token']);
    }

    public function testLoginInvalidCredentials()
    {
        $this->client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }
}
