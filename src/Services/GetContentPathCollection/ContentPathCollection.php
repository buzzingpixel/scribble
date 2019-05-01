<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentPathCollection;

use BuzzingPixel\Scribble\Abstracts\Collection;
use BuzzingPixel\Scribble\Services\GetContentFromPath\ContentCollection;

/**
 * @method ContentPathCollection subSet(int $limit, int $start = 0)
 * @method ContentCollection[] all() : array
 * @method ContentCollection|null first()
 * @method ContentCollection|null last()
 * @method ContentCollection|null current()()
 */
class ContentPathCollection extends Collection
{
    /** @var string */
    protected static $collectionClassInstance = ContentCollection::class;
}
