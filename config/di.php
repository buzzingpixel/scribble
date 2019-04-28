<?php

declare(strict_types=1);

use BuzzingPixel\Scribble\ScribbleApi;
use BuzzingPixel\Scribble\ScribbleApiContract;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use cebe\markdown\GithubMarkdown;
use Hyn\Frontmatter\Frontmatters\JsonFrontmatter;
use Hyn\Frontmatter\Parser as FontMatterParser;
use Psr\Container\ContainerInterface;

return [
    GetContentFromFile::class => static function () {
        $parser = new FontMatterParser(new GithubMarkdown());

        $parser->setFrontmatter(JsonFrontmatter::class);

        return new GetContentFromFile(
            new FontMatterParser(new GithubMarkdown())
        );
    },
    ScribbleApi::class => static function (ContainerInterface $di) {
        return new ScribbleApi($di);
    },
    ScribbleApiContract::class => static function (ContainerInterface $di) {
        return $di->get(ScribbleApi::class);
    },
];
