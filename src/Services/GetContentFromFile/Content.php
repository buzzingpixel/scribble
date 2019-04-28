<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromFile;

use Adbar\Dot;
use LogicException;

class Content
{
    /** @var bool */
    private $isInstantiated = false;

    /** @var string */
    private $markdown;
    /** @var string */
    private $html;
    /** @var mixed[] */
    private $meta;
    /** @var Dot */
    private $dot;

    /**
     * @param mixed[] $meta
     */
    public function __construct(string $markdown, string $html, array $meta)
    {
        if ($this->isInstantiated) {
            throw new LogicException(
                'Instance may only be instantiated once'
            );
        }

        $this->isInstantiated = true;

        $this->markdown = $markdown;
        $this->html     = $html;
        $this->meta     = $meta;
        $this->dot      = new Dot($meta);

        $this->isInstantiated = true;
    }

    public function markdown() : string
    {
        return $this->markdown;
    }

    public function html() : string
    {
        return $this->html;
    }

    /**
     * @return mixed[]
     */
    public function meta() : array
    {
        return $this->meta;
    }

    /**
     * @return mixed
     */
    public function getMetaItem(string $dotKey)
    {
        return $this->dot->get($dotKey);
    }
}
