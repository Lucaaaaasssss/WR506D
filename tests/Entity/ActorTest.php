<?php

namespace App\Tests\Entity;

use App\Entity\Actor;
use App\Entity\Movie;
use PHPUnit\Framework\TestCase;

class ActorTest extends TestCase
{
    public function testActorCreation(): void
    {
        $actor = new Actor();

        $this->assertNull($actor->getId());
        $this->assertNull($actor->getLastname());
        $this->assertNull($actor->getFirstname());
        $this->assertInstanceOf(\DateTime::class, $actor->getCreatedAt());
    }

    public function testActorSettersAndGetters(): void
    {
        $actor = new Actor();

        $actor->setLastname('Doe');
        $actor->setFirstname('John');
        $actor->setBio('Une biographie de test pour cet acteur.');

        $dob = new \DateTime('1990-05-15');
        $actor->setDob($dob);

        $this->assertEquals('Doe', $actor->getLastname());
        $this->assertEquals('John', $actor->getFirstname());
        $this->assertEquals('Une biographie de test pour cet acteur.', $actor->getBio());
        $this->assertEquals($dob, $actor->getDob());
    }

    public function testActorDeathDate(): void
    {
        $actor = new Actor();

        $dod = new \DateTime('2020-01-01');
        $actor->setDod($dod);

        $this->assertEquals($dod, $actor->getDod());
    }

    public function testActorMoviesCollection(): void
    {
        $actor = new Actor();
        $movie = new Movie();

        $this->assertCount(0, $actor->getMovies());

        $actor->addMovie($movie);
        $this->assertCount(1, $actor->getMovies());
        $this->assertTrue($actor->getMovies()->contains($movie));

        $actor->removeMovie($movie);
        $this->assertCount(0, $actor->getMovies());
    }

    public function testActorAddSameMovieTwice(): void
    {
        $actor = new Actor();
        $movie = new Movie();

        $actor->addMovie($movie);
        $actor->addMovie($movie);

        $this->assertCount(1, $actor->getMovies());
    }
}
