<?php

namespace App\DataFixtures;

use App\Entity\Liste;
use App\Entity\Tache;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // create 20 lists of 20 tasks! Bam!
        for ($i = 0; $i < 20; $i++) {
            $list = new Liste();
            $list->setNom('Liste n° '.$i);
            for ($j = 0; $j < 20; $j++) {
                $task = new Tache();
                $task->setTitre('Tâche n° '.$j);
                $task->setFait(false);
                $task->setListe($list);
                $manager->persist($task);
            }
            $manager->persist($list);
        }

        $manager->flush();
    }
}
