<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromFile;

use Hyn\Frontmatter\Parser as FontMatterParser;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use function array_merge;
use function is_array;

class GetContentFromFile
{
    /** @var FontMatterParser */
    private $frontMatterParser;

    public function __construct(FontMatterParser $frontMatterParser)
    {
        $this->frontMatterParser = $frontMatterParser;
    }

    public function get(SplFileInfo $file, GetContentFromFileDelegate $handler) : void
    {
        try {
            $content = $this->frontMatterParser->parse($file->getContents());

            $meta = array_merge(
                is_array($content['meta']) ? $content['meta'] : [],
                [
                    'baseName' => $file->getBasename(),
                    'baseNameNoExtension' => $file->getBasename('.' . $file->getExtension()),
                    'fileExtension' => $file->getExtension(),
                    'pathName' => $file->getPathname(),
                    'path' => $file->getPath(),
                ]
            );

            $handler->contentRetrieved(new Content(
                $content['markdown'] ?? '',
                $content['html'] ?? '',
                $meta
            ));
        } catch (Throwable $e) {
            $handler->unableToParseFile();
        }
    }
}
