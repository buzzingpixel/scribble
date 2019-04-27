<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Overrides;

use InvalidArgumentException;
use Symfony\Component\Finder\SplFileInfo as SymfonySplFileInfo;
use function dirname;
use function file_exists;

class SplFileInfo extends SymfonySplFileInfo
{
    public function __construct(string $file)
    {
        if (! file_exists($file)) {
            throw new InvalidArgumentException(
                'Specified file does not exist'
            );
        }

        parent::__construct(
            $file,
            dirname($file),
            $file
        );
    }
}
