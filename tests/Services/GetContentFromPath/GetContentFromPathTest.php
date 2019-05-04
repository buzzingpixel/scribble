<?php

declare(strict_types=1);

namespace BuzzingPixel\Tests\Services\GetContentFromPath;

use BuzzingPixel\Scribble\Factories\SymfonyFinderFactory;
use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromPath\ContentCollection;
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

            /** @var ContentCollection|null */
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

            public function collection() : ?ContentCollection
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

            public function contentRetrieved(ContentCollection $collection) : void
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

        /** @var ContentCollection $collection */
        $collection = $handler->collection();

        self::assertInstanceOf(ContentCollection::class, $collection);

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

        /** @var ContentCollection $collection */
        $collection = $handler->collection();

        self::assertInstanceOf(ContentCollection::class, $collection);

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

        self::assertNull($handler->collection());
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

        /** @var ContentCollection $collection */
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
            new ContentCollection(['asdf']);
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

        /** @var ContentCollection $collection */
        $collection = $handler->collection();

        $subCollection = $collection->subSet(2);

        $subCollectionNull = $collection->subSet(0);

        $first = $collection->first();

        $last = $collection->last();

        self::assertCount(4, $collection);

        self::assertCount(2, $subCollection);

        self::assertNull($subCollectionNull);

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

        $exception = null;

        try {
            new ContentCollection([]);
        } catch (InvalidArgumentException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(
            InvalidArgumentException::class,
            $exception
        );

        self::assertEquals(
            'Input items must not be empty',
            $exception->getMessage()
        );
    }

    /**
     * @throws Throwable
     */
    public function testFilterMetaEqualTo() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler,
            ['md', 'json']
        );

        /** @var ContentCollection $collection */
        $collection = $handler->collection();

        $md = $collection->filterMetaEqualTo('fileExtension', 'md');

        $json = $collection->filterMetaEqualTo('fileExtension', 'json');

        $noResults = $collection->filterMetaEqualTo(
            'path',
            'foo'
        );

        self::assertNull($noResults);

        self::assertEquals(3, $md->count());

        self::assertEquals(1, $json->count());

        self::assertEquals(4, $collection->count());

        $mdLoopCount = 0;

        foreach ($md->all() as $content) {
            $mdLoopCount++;

            self::assertEquals(
                'md',
                $content->getMetaItem('fileExtension')
            );
        }

        self::assertEquals(3, $mdLoopCount);

        $jsonLoopCount = 0;

        foreach ($json->all() as $content) {
            $jsonLoopCount++;

            self::assertEquals(
                'json',
                $content->getMetaItem('fileExtension')
            );
        }

        self::assertEquals(1, $jsonLoopCount);
    }

    /**
     * @throws Throwable
     */
    public function testFilterMetaNotEqualTo() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler,
            ['md', 'json']
        );

        /** @var ContentCollection $collection */
        $collection = $handler->collection();

        $md = $collection->filterMetaNotEqualTo('fileExtension', 'json');

        $json = $collection->filterMetaNotEqualTo('fileExtension', 'md');

        $noResults = $collection->filterMetaNotEqualTo(
            'path',
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory'
        );

        self::assertNull($noResults);

        self::assertEquals(3, $md->count());

        self::assertEquals(1, $json->count());

        self::assertEquals(4, $collection->count());

        $mdLoopCount = 0;

        foreach ($md->all() as $content) {
            $mdLoopCount++;

            self::assertEquals(
                'md',
                $content->getMetaItem('fileExtension')
            );
        }

        self::assertEquals(3, $mdLoopCount);

        $jsonLoopCount = 0;

        foreach ($json->all() as $content) {
            $jsonLoopCount++;

            self::assertEquals(
                'json',
                $content->getMetaItem('fileExtension')
            );
        }

        self::assertEquals(1, $jsonLoopCount);
    }

    /**
     * @throws Throwable
     */
    public function testFilterMetaIn() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler,
            ['md', 'json']
        );

        /** @var ContentCollection $collection */
        $collection = $handler->collection();

        $md = $collection->filterMetaIn('fileExtension', ['md']);

        $json = $collection->filterMetaIn('fileExtension', ['json']);

        $noResults = $collection->filterMetaIn('fileExtension', ['foo']);

        $noResults2 = $collection->filterMetaIn('foo-not-exists', ['bar']);

        self::assertNull($noResults);

        self::assertNull($noResults2);

        self::assertEquals(3, $md->count());

        self::assertEquals(1, $json->count());

        self::assertEquals(4, $collection->count());

        $mdLoopCount = 0;

        foreach ($md->all() as $content) {
            $mdLoopCount++;

            self::assertEquals(
                'md',
                $content->getMetaItem('fileExtension')
            );
        }

        self::assertEquals(3, $mdLoopCount);

        $jsonLoopCount = 0;

        foreach ($json->all() as $content) {
            $jsonLoopCount++;

            self::assertEquals(
                'json',
                $content->getMetaItem('fileExtension')
            );
        }

        self::assertEquals(1, $jsonLoopCount);
    }

    /**
     * @throws Throwable
     */
    public function testFilterMetaNotIn() : void
    {
        $service = Di::diContainer()->get(GetContentFromPath::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentFromPath/ContentDirectory',
            $handler,
            ['md', 'json']
        );

        /** @var ContentCollection $collection */
        $collection = $handler->collection();

        $md = $collection->filterMetaNotIn('fileExtension', ['json']);

        $json = $collection->filterMetaNotIn('fileExtension', ['md']);

        $noResults = $collection->filterMetaNotIn('fileExtension', [
            'md',
            'json',
        ]);

        self::assertNull($noResults);

        self::assertEquals(3, $md->count());

        self::assertEquals(1, $json->count());

        self::assertEquals(4, $collection->count());

        $mdLoopCount = 0;

        foreach ($md->all() as $content) {
            $mdLoopCount++;

            self::assertEquals(
                'md',
                $content->getMetaItem('fileExtension')
            );
        }

        self::assertEquals(3, $mdLoopCount);

        $jsonLoopCount = 0;

        foreach ($json->all() as $content) {
            $jsonLoopCount++;

            self::assertEquals(
                'json',
                $content->getMetaItem('fileExtension')
            );
        }

        self::assertEquals(1, $jsonLoopCount);
    }
}
