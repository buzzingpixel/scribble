<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromFile;

interface GetContentFromFileDelegate
{
    public function unableToParseFile() : void;

    public function contentRetrievedSuccessfully(Content $content) : void;
}
