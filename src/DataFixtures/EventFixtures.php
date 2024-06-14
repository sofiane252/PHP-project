<?php 

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTime;

class EventFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $isPublic = false;
        for ($i = 1; $i < 50; $i++) {
            if ($i == 25) {
                $isPublic = true;
            }

            $event = new Event();
            $event->setTitre('Événement ' . $i);
            $event->setDescription("Description événement " . $i);
            $event->setDate(new DateTime("2024-07-24"));
            $event->setHeure(($i + 1)%24);
            $event->setNbrMaxParticipants(mt_rand(50, 250));
            $event->setPublique($isPublic);

            // Récupérer un utilisateur de référence aléatoire
            $userReference = $this->getReference('user_' . mt_rand(0, 3));
            $event->setCreator($userReference);

            $manager->persist($event);
        }

        $manager->flush();
    }
}
