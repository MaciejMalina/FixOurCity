<?php

namespace App\Controller;

use App\Entity\Report;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    #[Route('/api/reports', name: 'get_reports', methods: ['GET'])]
    public function getReports(EntityManagerInterface $em): JsonResponse
    {
        $reports = $em->getRepository(Report::class)->findAll();

        $data = array_map(function ($report) {
            return [
                'id' => $report->getId(),
                'title' => $report->getTitle(),
                'content' => $report->getContent(),
                'status' => $report->getStatus(),
                'createdAt' => $report->getCreatedAt()->format('Y-m-d H:i'),
                'user' => $report->getUser()->getEmail(),
            ];
        }, $reports);

        return $this->json($data);
    }

    #[Route('/api/reports', name: 'create_report', methods: ['POST'])]
    public function createReport(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'], $data['content'], $data['userId'])) {
            return $this->json(['error' => 'Missing fields'], 400);
        }

        $user = $em->getRepository(User::class)->find($data['userId']);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $report = new Report();
        $report->setTitle($data['title']);
        $report->setContent($data['content']);
        $report->setUser($user);

        $em->persist($report);
        $em->flush();

        return $this->json(['message' => 'Report created'], 201);
    }
}
