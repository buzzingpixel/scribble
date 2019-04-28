<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble;

use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromFile\SplFileInfo;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPath;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPathDelegate;
use Psr\Container\ContainerInterface;

class ScribbleApi implements ScribbleApiContract
{
    /** @var ContainerInterface */
    private $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function getContentFromFile(
        string $filePath,
        GetContentFromFileDelegate $handler
    ) : void {
        $this->di->get(GetContentFromFile::class)->get(
            new SplFileInfo($filePath),
            $handler
        );
    }

    /**
     * @param string[] $extensions
     */
    public function getContentFromPath(
        string $dir,
        GetContentFromPathDelegate $handler,
        array $extensions = ['md']
    ) : void {
        $this->di->get(GetContentFromPath::class)->get(
            $dir,
            $handler,
            $extensions
        );
    }
}
