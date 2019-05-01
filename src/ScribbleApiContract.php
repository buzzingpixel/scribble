<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble;

use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPathDelegate;
use BuzzingPixel\Scribble\Services\GetContentPathCollection\GetContentPathCollectionDelegate;

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

    /**
     * Gets a path collection of content (listing)
     *
     * @param string[] $extensions
     */
    public function getContentPathCollection(
        string $dir,
        GetContentPathCollectionDelegate $handler,
        array $extensions = ['md']
    ) : void;
}
