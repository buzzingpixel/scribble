<?php

declare(strict_types=1);

use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use cebe\markdown\GithubMarkdown;
use Hyn\Frontmatter\Frontmatters\JsonFrontmatter;
use Hyn\Frontmatter\Parser as FontMatterParser;

return [
    GetContentFromFile::class => static function () {
        $parser = new FontMatterParser(new GithubMarkdown());

        $parser->setFrontmatter(JsonFrontmatter::class);

        return new GetContentFromFile(
            new FontMatterParser(new GithubMarkdown())
        );
    },
];
