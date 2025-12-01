<?php

namespace App\DataFixtures;

use App\Entity\Director;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Xylis\FakerCinema\Provider\Person;

class DirectorFixtures extends Fixture
{
    public const DIRECTOR_REFERENCE = 'director_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Person($faker));

        for ($i = 0; $i < 50; $i++) {
            $director = new Director();
            $director->setLastname($faker->lastName);
            $director->setFirstname($faker->firstName);

            // Date de naissance aléatoire entre 1930 et 1990
            $dob = $faker->dateTimeBetween('-90 years', '-30 years');
            $director->setDob(\DateTime::createFromInterface($dob));

            // 15% de chance d'avoir une date de décès
            if ($faker->boolean(15)) {
                $dod = $faker->dateTimeBetween($dob, 'now');
                $director->setDod(\DateTime::createFromInterface($dod));
            }

            $manager->persist($director);

            // Ajouter une référence pour pouvoir l'utiliser dans MovieFixtures
            $this->addReference(self::DIRECTOR_REFERENCE . $i, $director);
        }

        $manager->flush();
    }
}
