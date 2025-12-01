<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Director;
use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Xylis\FakerCinema\Provider\Movie as MovieProvider;

class MovieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new MovieProvider($faker));

        for ($i = 0; $i < 100; $i++) {
            $movie = new Movie();
            $movie->setName($faker->movie);
            $movie->setDescription($faker->overview);
            $movie->setDuration($faker->numberBetween(60, 240));

            // Date de sortie aléatoire entre 1950 et aujourd'hui
            $releaseDate = $faker->dateTimeBetween('-70 years', 'now');
            $movie->setReleaseData(\DateTime::createFromInterface($releaseDate));

            $movie->setImage($faker->imageUrl(640, 480, 'movies'));
            $movie->setNbEntries($faker->numberBetween(100000, 10000000));
            $movie->setUrl($faker->url);
            $movie->setBudget($faker->randomFloat(2, 1000000, 200000000));

            // Assigner un réalisateur aléatoire
            $directorReference = DirectorFixtures::DIRECTOR_REFERENCE . $faker->numberBetween(0, 49);
            $director = $this->getReference($directorReference, Director::class);
            $movie->setDirector($director);

            // 20% de chance d'être un brouillon
            $movie->setDraft($faker->boolean(20));

            // 30% de chance d'être offline
            $movie->setOnline($faker->boolean(70));

            // Ajouter entre 2 et 5 acteurs aléatoires
            $nbActors = $faker->numberBetween(2, 5);
            for ($j = 0; $j < $nbActors; $j++) {
                $actorReference = ActorFixtures::ACTOR_REFERENCE . $faker->numberBetween(0, 99);
                $actor = $this->getReference($actorReference, Actor::class);
                $movie->addActor($actor);
            }

            $manager->persist($movie);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActorFixtures::class,
            DirectorFixtures::class,
        ];
    }
}
