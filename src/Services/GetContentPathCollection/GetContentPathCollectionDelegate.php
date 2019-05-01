<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentPathCollection;

interface GetContentPathCollectionDelegate
{
    public function unableToParsePath() : void;

    public function noResults() : void;

    public function contentRetrieved(ContentPathCollection $collection) : void;
}
