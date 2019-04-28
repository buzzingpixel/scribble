<?php

declare(strict_types=1);

namespace BuzzingPixel\Tests\Factories;

use BuzzingPixel\Scribble\Factories\SymfonyFinderFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class SymfonyFinderFactoryTest extends TestCase
{
    public function testCreateFinder() : void
    {
        self::assertInstanceOf(
            Finder::class,
            (new SymfonyFinderFactory())->createFinder()
        );
    }
}
