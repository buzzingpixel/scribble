<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Abstracts;

use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;
use Countable;
use Iterator;

interface CollectionContract extends Countable, Iterator
{
    /**
     * @param Content[] $collection
     */
    public function __construct(iterable $collection);

    /**
     * Returns a new collection with specified subset of results
     * Implementing class should use phpdoc for class to note method return
     *
     * @return mixed
     */
    public function subSet(int $limit, int $start = 0);

    /**
     * Returns all items in collection
     *
     * @return mixed[]
     */
    public function all() : array;

    /**
     * Returns the first item in the collection
     * Implementing class should use phpdoc for class to note method return
     *
     * @return mixed|null
     */
    public function first();

    /**
     * Returns the last item in the collection
     * Implementing class should use phpdoc for class to note method return
     *
     * @return mixed|null
     */
    public function last();
}
