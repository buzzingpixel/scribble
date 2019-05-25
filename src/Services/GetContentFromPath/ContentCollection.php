<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromPath;

use BuzzingPixel\Scribble\Abstracts\Collection;
use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;
use function in_array;

/**
 * @method ContentCollection|null subSet(int $limit, int $start = 0)
 * @method ContentCollection reverseSortOrder()
 * @method Content[] all() : array
 * @method Content|null first()
 * @method Content|null last()
 * @method Content|null getItemAtIndex(int $index)
 * @method Content|null current()
 */
class ContentCollection extends Collection
{
    /** @var string */
    protected static $collectionClassInstance = Content::class;

    /**
     * @param mixed $metaVal
     */
    public function filterMetaEqualTo(string $metaKey, $metaVal) : ?ContentCollection
    {
        $contents = [];

        foreach ($this->all() as $content) {
            if ($content->getMetaItem($metaKey) !== $metaVal) {
                continue;
            }

            $contents[] = $content;
        }

        if (! $contents) {
            return null;
        }

        return new static($contents);
    }

    /**
     * @param mixed $metaVal
     */
    public function filterMetaNotEqualTo(string $metaKey, $metaVal) : ?ContentCollection
    {
        $contents = [];

        foreach ($this->all() as $content) {
            if ($content->getMetaItem($metaKey) === $metaVal) {
                continue;
            }

            $contents[] = $content;
        }

        if (! $contents) {
            return null;
        }

        return new static($contents);
    }

    /**
     * @param mixed[] $in
     */
    public function filterMetaIn(string $metaKey, array $in) : ?ContentCollection
    {
        $contents = [];

        foreach ($this->all() as $content) {
            if (! in_array(
                $content->getMetaItem($metaKey),
                $in
            )) {
                continue;
            }

            $contents[] = $content;
        }

        if (! $contents) {
            return null;
        }

        return new static($contents);
    }

    /**
     * @param mixed[] $in
     */
    public function filterMetaNotIn(string $metaKey, array $in) : ?ContentCollection
    {
        $contents = [];

        foreach ($this->all() as $content) {
            if (in_array(
                $content->getMetaItem($metaKey),
                $in
            )) {
                continue;
            }

            $contents[] = $content;
        }

        if (! $contents) {
            return null;
        }

        return new static($contents);
    }
}
