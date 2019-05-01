<?php

declare(strict_types=1);

use BuzzingPixel\Scribble\Factories\SymfonyFinderFactory;
use BuzzingPixel\Scribble\ScribbleApi;
use BuzzingPixel\Scribble\ScribbleApiContract;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPath;
use BuzzingPixel\Scribble\Services\GetContentPathCollection\GetContentPathCollection;
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
    GetContentFromPath::class => static function (ContainerInterface $di) {
        return new GetContentFromPath(
            $di->get(SymfonyFinderFactory::class),
            $di->get(GetContentFromFile::class)
        );
    },
    GetContentPathCollection::class => static function (ContainerInterface $di) {
        return new GetContentPathCollection(
            $di->get(SymfonyFinderFactory::class),
            $di->get(GetContentFromPath::class),
            $di->get(GetContentFromFile::class)
        );
    },
    ScribbleApi::class => static function (ContainerInterface $di) {
        return new ScribbleApi($di);
    },
    ScribbleApiContract::class => static function (ContainerInterface $di) {
        return $di->get(ScribbleApi::class);
    },
    SymfonyFinderFactory::class => static function () {
        return new SymfonyFinderFactory();
    },
];
