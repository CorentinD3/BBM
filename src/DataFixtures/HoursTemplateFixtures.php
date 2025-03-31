<?php

namespace App\DataFixtures;

use App\Entity\HoursTemplate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class HoursTemplateFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créez des heures de 9h00 à 19h00 par tranches de 30 minutes
        $startTime = new \DateTime('09:00');
        $endTime = new \DateTime('19:00');

        $interval = new \DateInterval('PT15M'); // 30 minutes interval

        while ($startTime <= $endTime) {
            $hoursTemplate = new HoursTemplate();
            $hoursTemplate->setHour(clone $startTime);

            // Persistez l'heure dans la base de données
            $manager->persist($hoursTemplate);

            // Ajoutez 30 minutes à l'heure actuelle
            $startTime->add($interval);
        }

        // Exécutez toutes les opérations de persistance
        $manager->flush();
    }
}

//php bin/console doctrine:fixtures:load --append