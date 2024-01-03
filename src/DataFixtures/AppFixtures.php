<?php

namespace App\DataFixtures;

use App\Entity\Status;
use App\Entity\Task;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $task = new Task();
            $task->setTitle($i . ' task')
                ->setCreatedAt(new DateTimeImmutable())
                ->setStatus(Status::New);
            $manager->persist($task);
        }

        $manager->flush();
    }
}
