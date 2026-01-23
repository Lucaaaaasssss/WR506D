<?php

namespace App\Tests\Entity;

use App\Entity\Movie;
use App\Entity\Actor;
use App\Entity\Category;
use App\Entity\Director;
use PHPUnit\Framework\TestCase;

class MovieTest extends TestCase
{
    public function testMovieCreation(): void
    {
        $movie = new Movie();

        $this->assertNull($movie->getId());
        $this->assertNull($movie->getName());
        // createdAt est défini par le lifecycle callback PrePersist, donc null à la création
        $this->assertNull($movie->getCreatedAt());
    }

    public function testMovieSettersAndGetters(): void
    {
        $movie = new Movie();

        $movie->setName('Test Movie');
        $movie->setDescription('A test movie description');
        $movie->setDuration(120);
        $movie->setBudget(1000000);
        $movie->setDraft(false);
        $movie->setOnline(true);

        $this->assertEquals('Test Movie', $movie->getName());
        $this->assertEquals('A test movie description', $movie->getDescription());
        $this->assertEquals(120, $movie->getDuration());
        $this->assertEquals(1000000, $movie->getBudget());
        $this->assertFalse($movie->isDraft());
        $this->assertTrue($movie->isOnline());
    }

    public function testMovieActorsCollection(): void
    {
        $movie = new Movie();
        $actor = new Actor();

        $this->assertCount(0, $movie->getActors());

        $movie->addActor($actor);
        $this->assertCount(1, $movie->getActors());
        $this->assertTrue($movie->getActors()->contains($actor));

        $movie->removeActor($actor);
        $this->assertCount(0, $movie->getActors());
    }

    public function testMovieCategoriesCollection(): void
    {
        $movie = new Movie();
        $category = new Category();

        $this->assertCount(0, $movie->getCategories());

        $movie->addCategory($category);
        $this->assertCount(1, $movie->getCategories());
        $this->assertTrue($movie->getCategories()->contains($category));

        $movie->removeCategory($category);
        $this->assertCount(0, $movie->getCategories());
    }

    public function testMovieDirector(): void
    {
        $movie = new Movie();
        $director = new Director();

        $this->assertNull($movie->getDirector());

        $movie->setDirector($director);
        $this->assertEquals($director, $movie->getDirector());
    }
}
