<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble;

use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;

interface ScribbleApiContract
{
    public function getContentFromFile(string $filePath, GetContentFromFileDelegate $handler) : void;
}
