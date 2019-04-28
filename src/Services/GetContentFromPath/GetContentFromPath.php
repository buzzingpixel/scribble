<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromPath;

use BuzzingPixel\Scribble\Factories\SymfonyFinderFactory;
use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use function in_array;

class GetContentFromPath
{
    /** @var SymfonyFinderFactory */
    private $symfonyFinderFactory;
    /** @var GetContentFromFile */
    private $getContentFromFile;

    public function __construct(
        SymfonyFinderFactory $symfonyFinderFactory,
        GetContentFromFile $getContentFromFile
    ) {
        $this->symfonyFinderFactory = $symfonyFinderFactory;
        $this->getContentFromFile   = $getContentFromFile;
    }

    /**
     * @param string[] $extensions
     */
    public function get(
        string $dir,
        GetContentFromPathDelegate $handler,
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
        GetContentFromPathDelegate $handler,
        array $extensions = ['md']
    ) : void {
        $finder = $this->symfonyFinderFactory->createFinder()
            ->files()
            ->in($dir)
            ->filter(static function (SplFileInfo $file) use ($extensions) : bool {
                return in_array($file->getExtension(), $extensions);
            })
            ->sortByName(true);

        if (! $finder->hasResults()) {
            $handler->noResults();

            return;
        }

        $parsedFiles = [];

        foreach ($finder as $file) {
            $content = $this->processFile($file);

            if (! $content) {
                continue;
            }

            $parsedFiles[] = $content;
        }

        $handler->contentRetrieved(
            new ContentCollection($parsedFiles)
        );
    }

    private function processFile(SplFileInfo $file) : ?Content
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

        $this->getContentFromFile->get($file, $handler);

        return $handler->content();
    }
}
