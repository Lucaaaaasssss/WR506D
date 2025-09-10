<?php

namespace App\Service;

use Cocur\Slugify\Slugify;

class SlugifyService
{
    private Slugify $slugger;

    public function __construct()
    {
        $this->slugger = new Slugify(['locale' => 'fr']);
    }

    public function slugify(string $text): string
    {
        return $this->slugger->slugify($text);
    }
}
