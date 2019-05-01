<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentPathCollection;

use BuzzingPixel\Scribble\Factories\SymfonyFinderFactory;
use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromFile\SplFileInfo as CustomSplFileInfo;
use BuzzingPixel\Scribble\Services\GetContentFromPath\ContentCollection;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPath;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPathDelegate;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use function in_array;

class GetContentPathCollection
{
    /** @var SymfonyFinderFactory */
    private $symfonyFinderFactory;
    /** @var GetContentFromPath */
    private $getContentFromPath;
    /** @var GetContentFromFile */
    private $getContentFromFile;

    public function __construct(
        SymfonyFinderFactory $symfonyFinderFactory,
        GetContentFromPath $getContentFromPath,
        GetContentFromFile $getContentFromFile
    ) {
        $this->symfonyFinderFactory = $symfonyFinderFactory;
        $this->getContentFromPath   = $getContentFromPath;
        $this->getContentFromFile   = $getContentFromFile;
    }

    /**
     * @param string[] $extensions
     */
    public function get(
        string $dir,
        GetContentPathCollectionDelegate $handler,
        array $extensions = ['md']
    ) : void {
        try {
            $this->innerGet($dir, $handler, $extensions);
        } catch (Throwable $e) {
            $handler->unableToParsePath();
        }
    }

    /**
     * @param string[] $extensions
     */
    private function innerGet(
        string $dir,
        GetContentPathCollectionDelegate $handler,
        array $extensions
    ) : void {
        $finder = $this->symfonyFinderFactory->createFinder()
            ->in($dir)
            ->depth(0)
            ->sortByName(true);

        if (! $finder->hasResults()) {
            $handler->noResults();

            return;
        }

        $collection = [];

        foreach ($finder as $fileInfo) {
            $content = $this->processFileInfo($fileInfo, $extensions);

            if (! $content) {
                continue;
            }

            $collection[] = $content;
        }

        if (! $collection) {
            $handler->noResults();

            return;
        }

        $handler->contentRetrieved(
            new ContentPathCollection($collection)
        );
    }

    /**
     * @param string[] $extensions
     */
    private function processFileInfo(SplFileInfo $fileInfo, array $extensions) : ?ContentCollection
    {
        if ($fileInfo->isDir()) {
            return $this->getContentFromPath($fileInfo->getPathname(), $extensions);
        }

        if (! in_array(
            $fileInfo->getExtension(),
            $extensions,
            true
        )) {
            return null;
        }

        return $this->getContentFromFile($fileInfo->getPathname(), $extensions);
    }

    /**
     * @param string[] $extensions
     */
    private function getContentFromPath(string $path, array $extensions) : ?ContentCollection
    {
        $handler = new class implements GetContentFromPathDelegate {
            /** @var ContentCollection|null */
            private $collection;

            public function collection() : ?ContentCollection
            {
                return $this->collection;
            }

            public function unableToParsePath() : void
            {
            }

            public function noResults() : void
            {
            }

            public function contentRetrieved(ContentCollection $collection) : void
            {
                $this->collection = $collection;
            }
        };

        $this->getContentFromPath->get($path, $handler, $extensions);

        return $handler->collection();
    }

    /**
     * @param string[] $extensions
     */
    private function getContentFromFile(string $path, array $extensions) : ?ContentCollection
    {
        $handler = new class implements GetContentFromFileDelegate {
            /** @var Content|null */
            private $content;

            public function content() : ?Content
            {
                return $this->content;
            }

            public function unableToParseFile() : void
            {
            }

            public function contentRetrieved(Content $content) : void
            {
                $this->content = $content;
            }
        };

        $this->getContentFromFile->get(new CustomSplFileInfo($path), $handler);

        $content = $handler->content();

        if (! $handler->content()) {
            return null;
        }

        return new ContentCollection([$content]);
    }
}
