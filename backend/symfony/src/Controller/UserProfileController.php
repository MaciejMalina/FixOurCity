<?php
namespace App\Controller;

use App\Entity\UserProfile;
use App\Repository\UserProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/profile')]
class UserProfileController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(UserProfileRepository $repository): JsonResponse
    {
        return $this->json($repository->findAll());
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(UserProfileRepository $repository, int $id): JsonResponse
    {
        $profile = $repository->find($id);
        return $profile ? $this->json($profile) : $this->json(['error' => 'Not found'], 404);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $userRepo->find($data['user_id']);
        $profile = new UserProfile();
        $profile->setUser($user);
        $profile->setFirstName($data['firstName']);
        $profile->setLastName($data['lastName']);
        $em->persist($profile);
        $em->flush();

        return $this->json($profile, 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, UserProfileRepository $repo, EntityManagerInterface $em, int $id): JsonResponse
    {
        $profile = $repo->find($id);
        if (!$profile) return $this->json(['error' => 'Not found'], 404);

        $data = json_decode($request->getContent(), true);
        $profile->setFirstName($data['firstName'] ?? $profile->getFirstName());
        $profile->setLastName($data['lastName'] ?? $profile->getLastName());
        $em->flush();

        return $this->json($profile);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(UserProfileRepository $repo, EntityManagerInterface $em, int $id): JsonResponse
    {
        $profile = $repo->find($id);
        if (!$profile) return $this->json(['error' => 'Not found'], 404);

        $em->remove($profile);
        $em->flush();
        return $this->json(null, 204);
    }
}
