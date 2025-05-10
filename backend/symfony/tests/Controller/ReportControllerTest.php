<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Report;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private string $token;
    private array $createdReports = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $email = 'report_user_' . uniqid() . '@example.com';

        $user = new User();
        $user->setEmail($email)
            ->setPassword(password_hash('password123', PASSWORD_BCRYPT))
            ->setFirstName('Report')
            ->setLastName('Tester')
            ->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => $email,
            'password' => 'password123'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->token = $data['token'];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdReports as $id) {
            $report = $this->entityManager->getRepository(Report::class)->find($id);
            if ($report) {
                $this->entityManager->remove($report);
            }
        }
        $this->entityManager->flush();
        parent::tearDown();
    }

    public function testCreateReportSuccess(): void
    {
        $this->client->request('POST', '/api/reports', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $this->token
        ], json_encode([
            'title' => 'Zepsuty znak drogowy',
            'description' => 'Znak na skrzyżowaniu przewrócony.'
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->createdReports[] = $data['id'];
    }

    public function testCreateReportMissingFields(): void
    {
        $this->client->request('POST', '/api/reports', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $this->token
        ], json_encode([
            'title' => 'Niekompletne zgłoszenie'
        ]));

        $this->assertResponseStatusCodeSame(400);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertSame('Missing fields', $content['error']);
    }

    public function testListReports(): void
    {
        $this->client->request('GET', '/api/reports', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->token
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
        $this->assertIsArray(json_decode($this->client->getResponse()->getContent(), true));
    }
}
