<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromFile;

use Symfony\Component\Finder\SplFileInfo as SymfonySplFileInfo;
use function dirname;

class SplFileInfo extends SymfonySplFileInfo
{
    public function __construct(string $file)
    {
        parent::__construct(
            $file,
            dirname($file),
            $file
        );
    }
}
