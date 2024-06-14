<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $userNames = ['Alice', 'Bob', 'Charlie', 'Diana'];
        foreach ($userNames as $index => $name) {
            $user = new User();
            $user->setEmail(strtolower($name).'@example.com');
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword(password_hash('password123', PASSWORD_BCRYPT)); // Assurez-vous de hacher le mot de passe
            $user->setNom($name . "nom");
            $user->setPrenom($name);

            $this->addReference('user_' . $index, $user); // Ajouter une référence pour l'utilisateur
            $manager->persist($user);
        }

        $manager->flush();
    }
}

