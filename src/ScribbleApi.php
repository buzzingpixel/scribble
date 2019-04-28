<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble;

use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromFile\SplFileInfo;
use Psr\Container\ContainerInterface;

class ScribbleApi implements ScribbleApiContract
{
    /** @var ContainerInterface */
    private $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function getContentFromFile(string $filePath, GetContentFromFileDelegate $handler) : void
    {
        $this->di->get(GetContentFromFile::class)->get(
            new SplFileInfo($filePath),
            $handler
        );
    }
}
