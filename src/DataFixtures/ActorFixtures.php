<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Xylis\FakerCinema\Provider\Person;

class ActorFixtures extends Fixture
{
    public const ACTOR_REFERENCE = 'actor_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Person($faker));

        for ($i = 0; $i < 100; $i++) {
            $actor = new Actor();
            $actor->setLastname($faker->lastName);
            $actor->setFirstname($faker->firstName);

            // Date de naissance aléatoire entre 1920 et 2000
            $dob = $faker->dateTimeBetween('-100 years', '-20 years');
            $actor->setDob(\DateTime::createFromInterface($dob));

            // 20% de chance d'avoir une date de décès
            if ($faker->boolean(20)) {
                $dod = $faker->dateTimeBetween($dob, 'now');
                $actor->setDod(\DateTime::createFromInterface($dod));
            }

            $actor->setBio($faker->paragraph(3));

            $manager->persist($actor);

            // Ajouter une référence pour pouvoir l'utiliser dans MovieFixtures
            $this->addReference(self::ACTOR_REFERENCE . $i, $actor);
        }

        $manager->flush();
    }
}
