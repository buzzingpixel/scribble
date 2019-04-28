<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromPath;

interface GetContentFromPathDelegate
{
    public function unableToParsePath() : void;

    public function noResults() : void;

    public function contentRetrieved(ContentCollection $collection) : void;
}
