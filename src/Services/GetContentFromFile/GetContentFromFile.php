<?php

declare(strict_types=1);

namespace BuzzingPixel\Scribble\Services\GetContentFromFile;

use Hyn\Frontmatter\Parser as FontMatterParser;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
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

            $meta = $content['meta'];

            $handler->contentRetrieved(new Content(
                $content['markdown'] ?? '',
                $content['html'] ?? '',
                is_array($meta) ? $meta : []
            ));
        } catch (Throwable $e) {
            $handler->unableToParseFile();
        }
    }
}
