<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Movie;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testCategoryCreation(): void
    {
        $category = new Category();

        $this->assertNull($category->getId());
        $this->assertNull($category->getName());
        $this->assertInstanceOf(\DateTimeImmutable::class, $category->getCreatedAt());
    }

    public function testCategorySettersAndGetters(): void
    {
        $category = new Category();

        $category->setName('Action');

        $this->assertEquals('Action', $category->getName());
    }

    public function testCategoryMoviesCollection(): void
    {
        $category = new Category();
        $movie = new Movie();

        $this->assertCount(0, $category->getMovies());

        $category->addMovie($movie);
        $this->assertCount(1, $category->getMovies());
        $this->assertTrue($category->getMovies()->contains($movie));

        $category->removeMovie($movie);
        $this->assertCount(0, $category->getMovies());
    }

    public function testCategoryAddSameMovieTwice(): void
    {
        $category = new Category();
        $movie = new Movie();

        $category->addMovie($movie);
        $category->addMovie($movie);

        $this->assertCount(1, $category->getMovies());
    }

    public function testCategoryCreatedAt(): void
    {
        $category = new Category();
        $newDate = new \DateTimeImmutable('2024-01-01');

        $category->setCreatedAt($newDate);

        $this->assertEquals($newDate, $category->getCreatedAt());
    }
}
