<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromPath;

use BuzzingPixel\Scribble\Abstracts\Collection;
use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;

/**
 * @method ContentCollection subSet(int $limit, int $start = 0)
 * @method Content[] all() : array
 * @method Content|null first()
 * @method Content|null last()
 * @method Content|null current()
 */
class ContentCollection extends Collection
{
    /** @var string */
    protected static $collectionClassInstance = Content::class;
}
