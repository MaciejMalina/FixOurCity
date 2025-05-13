<?php

namespace App\DataFixtures;

use App\Entity\{User, Category, Status, Tag, Report, Comment, Image};
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pl_PL');

        $categories = [];
        foreach (['Infrastruktura', 'Zieleń', 'Bezpieczeństwo', 'Parking', 'Oświetlenie'] as $name) {
            $cat = new Category();
            $cat->setName($name);
            $manager->persist($cat);
            $categories[] = $cat;
        }

        $statuses = [];
        foreach (['Nowe', 'W trakcie', 'Zamknięte'] as $label) {
            $st = new Status();
            $st->setLabel($label);
            $manager->persist($st);
            $statuses[] = $st;
        }

        $tags = [];
        foreach (['dziura', 'latarnia', 'brak chodnika', 'złe oznakowanie', 'ulica'] as $name) {
            $tag = new Tag();
            $tag->setName($name);
            $manager->persist($tag);
            $tags[] = $tag;
        }

        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail("user$i@example.com");
            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $manager->persist($user);

            for ($j = 0; $j < 3; $j++) {
                $report = new Report();
                $report->setTitle($faker->sentence(3));
                $report->setDescription($faker->paragraph);
                $report->setLat($faker->latitude(50, 54));
                $report->setLng($faker->longitude(18, 22));
                $report->setUser($user);
                $report->setCategory($faker->randomElement($categories));
                $report->setStatus($faker->randomElement($statuses));

                foreach ($faker->randomElements($tags, rand(1, 3)) as $tag) {
                    $report->addTag($tag);
                }

                for ($k = 0; $k < rand(0, 2); $k++) {
                    $comment = new Comment();
                    $comment->setContent($faker->sentence);
                    $comment->setUser($user);
                    $comment->setReport($report);
                    $manager->persist($comment);
                }

                $image = new Image();
                $image->setUrl($faker->imageUrl(640, 480, 'city'));
                $image->setAlt($faker->words(2, true));
                $image->setReport($report);
                $manager->persist($image);

                if ($faker->boolean(50)) {
                    $user->addFollowedReport($report);
                }

                $manager->persist($report);
            }
        }

        $manager->flush();
    }
}
