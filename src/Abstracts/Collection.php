<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Abstracts;

use InvalidArgumentException;
use LogicException;
use function array_slice;

abstract class Collection implements CollectionContract
{
    /**
     * Implementing class should define this
     *
     * @var string
     */
    protected static $collectionClassInstance = '';

    /** @var bool */
    private $isInstantiated = false;

    /** @var int */
    private $count = 0;

    /** @var mixed[] */
    private $collection = [];

    /** @var int */
    private $index = 0;

    /**
     * @param mixed[] $collection
     */
    public function __construct(iterable $collection)
    {
        if ($this->isInstantiated) {
            throw new LogicException(
                'Instance may only be instantiated once'
            );
        }

        $this->isInstantiated = true;

        foreach ($collection as $instance) {
            if (! $instance instanceof static::$collectionClassInstance) {
                throw new InvalidArgumentException(
                    'Input items must be instance of ' . static::$collectionClassInstance
                );
            }

            $this->collection[] = $instance;

            $this->count++;
        }

        if (! $this->collection) {
            throw new InvalidArgumentException(
                'Input items must not be empty'
            );
        }
    }

    /**
     * Child class should use phpdoc for class to note method return
     *
     * @return mixed|null
     */
    public function subSet(int $limit, int $start = 0)
    {
        $contents = array_slice(
            $this->collection,
            $start,
            $limit
        );

        if (! $contents) {
            return null;
        }

        return new static($contents);
    }

    /**
     * Child class should use phpdoc for class to note method return
     *
     * @return mixed[]
     */
    public function all() : array
    {
        return $this->collection;
    }

    /**
     * Child class should use phpdoc for class to note method return
     *
     * @return mixed|null
     */
    public function first()
    {
        return $this->collection[0] ?? null;
    }

    /**
     * Child class should use phpdoc for class to note method return
     *
     * @return mixed|null
     */
    public function last()
    {
        return $this->collection[$this->count - 1] ?? null;
    }

    /**
     * Child class should use phpdoc for class to note method return
     *
     * @return mixed|null
     */
    public function current()
    {
        return $this->collection[$this->index] ?? null;
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
        return isset($this->collection[$this->key()]);
    }

    public function rewind() : void
    {
        $this->index = 0;
    }

    public function count() : int
    {
        return $this->count;
    }
}
