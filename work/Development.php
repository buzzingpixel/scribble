<?php

declare(strict_types=1);

namespace BuzzingPixel\Work;

use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromFile\SplFileInfo;
use corbomite\di\Di;
use function dd;

class Development
{
    public function __invoke() : void
    {
        $getContentFromFile = Di::diContainer()->get(GetContentFromFile::class);

        $file = new SplFileInfo(APP_DIR . '/work/content/index.md');

        $getContentFromFile->get($file, new class implements GetContentFromFileDelegate {
            public function unableToParseFile() : void
            {
                dd('unableToParseFile');
            }

            public function contentRetrieved(Content $content) : void
            {
                dd($content->getMetaItem('foo.bar'));
            }
        });
    }
}
