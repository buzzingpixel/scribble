<?php

declare(strict_types=1);

namespace BuzzingPixel\Work;

use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromFile\SplFileInfo;
use BuzzingPixel\Scribble\Services\GetContentFromPath\ContentCollection;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPath;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPathDelegate;
use BuzzingPixel\Scribble\Services\GetContentPathCollection\ContentPathCollection;
use BuzzingPixel\Scribble\Services\GetContentPathCollection\GetContentPathCollection;
use BuzzingPixel\Scribble\Services\GetContentPathCollection\GetContentPathCollectionDelegate;
use corbomite\di\Di;
use Throwable;
use function dd;

class Development
{
    /**
     * @throws Throwable
     */
    public function __invoke() : void
    {
        // $this->getContentFromFile();
        $this->getContentFromPath();
        // $this->getContentPathCollection();
    }

    /**
     * @throws Throwable
     */
    public function getContentPathCollection() : void
    {
        $getContentPathCollection = Di::diContainer()->get(GetContentPathCollection::class);

        $path = APP_DIR . '/work/content/TestContentCollection';

        $getContentPathCollection->get($path, new class implements GetContentPathCollectionDelegate {
            public function unableToParsePath() : void
            {
                dd(__METHOD__);
            }

            public function noResults() : void
            {
                dd(__METHOD__);
            }

            public function contentRetrieved(ContentPathCollection $collection) : void
            {
                dd($collection, __METHOD__);
            }
        });
    }

    /**
     * @throws Throwable
     */
    public function getContentFromPath() : void
    {
        $getContentFromPath = Di::diContainer()->get(GetContentFromPath::class);

        $path = APP_DIR . '/work/content/TestContentDirectory';

        $getContentFromPath->get($path, new class implements GetContentFromPathDelegate {
            public function unableToParsePath() : void
            {
                dd(__METHOD__);
            }

            public function noResults() : void
            {
                dd(__METHOD__);
            }

            public function contentRetrieved(ContentCollection $collection) : void
            {
                dd(
                    $collection->filterMetaNotIn(
                        'baseNameNoExtension',
                        [
                            'Test2',
                            'Test4',
                        ]
                    )
                );
            }
        });
    }

    /**
     * @throws Throwable
     */
    public function getContentFromFile() : void
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
