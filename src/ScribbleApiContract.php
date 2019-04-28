<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble;

use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPathDelegate;

interface ScribbleApiContract
{
    /**
     * Gets content from specified file path
     */
    public function getContentFromFile(
        string $filePath,
        GetContentFromFileDelegate $handler
    ) : void;

    /**
     * Gets content files recursively from specified directory
     *
     * @param string[] $extensions
     */
    public function getContentFromPath(
        string $dir,
        GetContentFromPathDelegate $handler,
        array $extensions = ['md']
    ) : void;
}
