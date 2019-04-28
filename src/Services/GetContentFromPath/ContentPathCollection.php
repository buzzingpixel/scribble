<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromPath;

use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;
use Countable;
use InvalidArgumentException;
use Iterator;
use LogicException;
use function array_reverse;
use function array_slice;

class ContentPathCollection implements Countable, Iterator
{
    /** @var bool */
    private $isInstantiated = false;

    /** @var int */
    private $count = 0;
    /** @var Content[] */
    private $contents = [];

    /** @var int */
    private $index = 0;

    /**
     * @param Content[] $contents
     */
    public function __construct(iterable $contents)
    {
        if ($this->isInstantiated) {
            throw new LogicException(
                'Instance may only be instantiated once'
            );
        }

        $this->isInstantiated = true;

        foreach ($contents as $content) {
            if (! $content instanceof Content) {
                throw new InvalidArgumentException(
                    'Input items must be instance of ' . Content::class
                );
            }

            $this->contents[] = $content;

            $this->count++;
        }
    }

    public function subSet(int $limit, int $start = 0) : ContentPathCollection
    {
        $contents = array_slice(
            $this->contents,
            $start,
            $limit
        );

        return new ContentPathCollection($contents);
    }

    public function count() : int
    {
        return $this->count;
    }

    /**
     * @return Content[]
     */
    public function all() : array
    {
        return $this->contents;
    }

    public function first() : ?Content
    {
        return $this->contents[0] ?? null;
    }

    public function last() : ?Content
    {
        return $this->contents[$this->count - 1] ?? null;
    }

    public function current() : Content
    {
        return $this->contents[$this->index];
    }

    public function next() : void
    {
        $this->index++;
    }

    public function key() : int
    {
        return $this->index;
    }

    public function valid() : bool
    {
        return isset($this->contents[$this->key()]);
    }

    public function rewind() : void
    {
        $this->index = 0;
    }

    public function reverse() : void
    {
        $this->contents = array_reverse($this->all());
        $this->rewind();
    }
}
