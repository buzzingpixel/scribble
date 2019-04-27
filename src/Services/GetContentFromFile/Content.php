<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromFile;

use Adbar\Dot;

class Content
{
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
        $this->markdown = $markdown;
        $this->html     = $html;
        $this->meta     = $meta;
        $this->dot      = new Dot($meta);
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
