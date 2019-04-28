<?php

declare(strict_types=1);

namespace BuzzingPixel\Tests\Services\GetContentFromPath;

use BuzzingPixel\Scribble\Factories\SymfonyFinderFactory;
use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromPath\ContentPathCollection;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPath;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPathDelegate;
use corbomite\di\Di;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

class GetContentFromPathTest extends TestCase
{
    private function getDelegate()
    {
        return new class implements GetContentFromPathDelegate {
            /** @var int */
            private $unableToParsePathCalls = 0;

            /** @var int */
            private $noResultsCalls = 0;

            /** @var int */
            private $contentRetrievedCalls = 0;

            /** @var ContentPathCollection|null */
            private $collection;

            public function unableToParsePathCalls() : int
            {
                return $this->unableToParsePathCalls;
            }

            public function noResultsCalls() : int
            {
                return $this->noResultsCalls;
            }

            public function contentRetrievedCalls() : int
            {
                return $this->contentRetrievedCalls;
            }

            public function collection() : ?ContentPathCollection
            {
                return $this->collection;
            }

            public function unableToParsePath() : void
            {
                $this->unableToParsePathCalls++;
            }

            public function noResults() : void
            {
                $this->noResultsCalls++;
            }

            public function contentRetrieved(ContentPathCollection $collection) : void
            {
                $this->contentRetrievedCalls++;
                $this->collection = $collection;
            }
        };
    }

    /**
     * @throws Throwable
     */
    public function testInvalidPath() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get('asdf', $handler);

        self::assertEquals(1, $handler->unableToParsePathCalls());

        self::assertEquals(0, $handler->noResultsCalls());

        self::assertEquals(0, $handler->contentRetrievedCalls());

        self::assertNull($handler->collection());
    }

    /**
     * @throws Throwable
     */
    public function testNoResults() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler,
            ['txt']
        );

        self::assertEquals(0, $handler->unableToParsePathCalls());

        self::assertEquals(1, $handler->noResultsCalls());

        self::assertEquals(0, $handler->contentRetrievedCalls());

        self::assertNull($handler->collection());
    }

    /**
     * @throws Throwable
     */
    public function testFilterExtensionJson() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler,
            ['json']
        );

        self::assertEquals(0, $handler->unableToParsePathCalls());

        self::assertEquals(0, $handler->noResultsCalls());

        self::assertEquals(1, $handler->contentRetrievedCalls());

        /** @var ContentPathCollection $collection */
        $collection = $handler->collection();

        self::assertInstanceOf(ContentPathCollection::class, $collection);

        self::assertCount(1, $collection);

        $content = $collection->all()[0];

        self::assertInstanceOf(Content::class, $content);

        self::assertEmpty($content->markdown());

        self::assertEmpty($content->html());

        self::assertEquals('bar', $content->getMetaItem('foo'));
    }

    /**
     * @throws Throwable
     */
    public function testFilterExtensionMdDefault() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler
        );

        self::assertEquals(0, $handler->unableToParsePathCalls());

        self::assertEquals(0, $handler->noResultsCalls());

        self::assertEquals(1, $handler->contentRetrievedCalls());

        /** @var ContentPathCollection $collection */
        $collection = $handler->collection();

        self::assertInstanceOf(ContentPathCollection::class, $collection);

        self::assertCount(3, $collection);

        foreach ($collection as $content) {
            self::assertInstanceOf(Content::class, $content);

            self::assertEquals(
                "\nTest content\n",
                $content->markdown()
            );

            self::assertEquals(
                "<p>Test content</p>\n",
                $content->html()
            );

            self::assertEquals('bar', $content->getMetaItem('foo'));

            self::assertEquals('baz', $content->getMetaItem('bar'));
        }
    }

    /**
     * @throws Throwable
     */
    public function testRetrieveEdgeCases() : void
    {
        $finderFactory = Di::diContainer()->get(SymfonyFinderFactory::class);

        $getContentFromFile = $this->createMock(GetContentFromFile::class);

        $getContentFromFile->expects(self::once())
            ->method('get')
            ->willReturnCallback(static function (
                SplFileInfo $file,
                GetContentFromFileDelegate $handler
            ) : void {
                $handler->unableToParseFile();
            });

        /** @noinspection PhpParamsInspection */
        $service = new GetContentFromPath($finderFactory, $getContentFromFile);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler,
            ['json']
        );

        self::assertCount(0, $handler->collection());
    }

    /**
     * @throws Throwable
     */
    public function testCollectionDoubleInstantiation() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler,
            ['json']
        );

        /** @var ContentPathCollection $collection */
        $collection = $handler->collection();

        $exception = null;

        try {
            $collection->__construct([]);
        } catch (LogicException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(LogicException::class, $exception);

        self::assertEquals(
            'Instance may only be instantiated once',
            $exception->getMessage()
        );
    }

    public function testCollectionInstances() : void
    {
        $exception = null;

        try {
            new ContentPathCollection(['asdf']);
        } catch (InvalidArgumentException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(LogicException::class, $exception);

        self::assertEquals(
            'Input items must be instance of ' . Content::class,
            $exception->getMessage()
        );
    }

    /**
     * @throws Throwable
     */
    public function testSubSets() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler,
            ['md', 'json']
        );

        /** @var ContentPathCollection $collection */
        $collection = $handler->collection();

        $subCollection = $collection->subSet(2);

        $first = $collection->first();

        $last = $collection->last();

        $nullFirst = (new ContentPathCollection([]))->first();

        $nullLast = (new ContentPathCollection([]))->last();

        self::assertCount(4, $collection);

        self::assertCount(2, $subCollection);

        self::assertEmpty($first->markdown());

        self::assertEmpty($first->html());

        self::assertEquals('bar', $first->getMetaItem('foo'));

        self::assertEquals(
            "\nTest content\n",
            $last->markdown()
        );

        self::assertEquals(
            "<p>Test content</p>\n",
            $last->html()
        );

        self::assertEquals('bar', $last->getMetaItem('foo'));

        self::assertEquals('baz', $last->getMetaItem('bar'));

        self::assertNull($nullFirst);

        self::assertNull($nullLast);
    }

    /**
     * @throws Throwable
     */
    public function testReverse() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler,
            ['md', 'json']
        );

        /** @var ContentPathCollection $collection */
        $collection = $handler->collection();

        $collection->reverse();

        $first = $collection->first();

        $last = $collection->last();

        self::assertEmpty($last->markdown());

        self::assertEmpty($last->html());

        self::assertEquals('bar', $last->getMetaItem('foo'));

        self::assertEquals(
            "\nTest content\n",
            $first->markdown()
        );

        self::assertEquals(
            "<p>Test content</p>\n",
            $first->html()
        );
    }
}
