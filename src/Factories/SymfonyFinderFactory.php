<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Factories;

use Symfony\Component\Finder\Finder;

class SymfonyFinderFactory
{
    public function createFinder() : Finder
    {
        return new Finder();
    }
}
