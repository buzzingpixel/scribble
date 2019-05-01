<?php

declare(strict_types=1);

namespace BuzzingPixel\Tests\Services\GetContentPathCollection;

use BuzzingPixel\Scribble\Factories\SymfonyFinderFactory;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPath;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPathDelegate;
use BuzzingPixel\Scribble\Services\GetContentPathCollection\ContentPathCollection;
use BuzzingPixel\Scribble\Services\GetContentPathCollection\GetContentPathCollection;
use BuzzingPixel\Scribble\Services\GetContentPathCollection\GetContentPathCollectionDelegate;
use corbomite\di\Di;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

class GetContentPathCollectionTest extends TestCase
{
    private function getDelegate()
    {
        return new class implements GetContentPathCollectionDelegate {
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
        $service = Di::diContainer()->get(GetContentPathCollection::class);

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
        $service = Di::diContainer()->get(GetContentPathCollection::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentPathCollection/ContentDirectory',
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
    public function testRetrieveEdgeCasesOne() : void
    {
        $finderFactory = Di::diContainer()->get(SymfonyFinderFactory::class);

        $getContentFromPath = $this->createMock(GetContentFromPath::class);

        $getContentFromPath->method('get')
            ->willReturnCallback(static function (
                string $path,
                GetContentFromPathDelegate $handler
            ) : void {
                $handler->unableToParsePath();
            });

        $getContentFromFile = $this->createMock(GetContentFromFile::class);

        $getContentFromFile->method('get')
            ->willReturnCallback(static function (
                SplFileInfo $file,
                GetContentFromFileDelegate $handler
            ) : void {
                $handler->unableToParseFile();
            });

        /** @noinspection PhpParamsInspection */
        $service = new GetContentPathCollection(
            $finderFactory,
            $getContentFromPath,
            $getContentFromFile
        );

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentPathCollection/ContentDirectory',
            $handler,
            ['json']
        );

        self::assertEquals(0, $handler->unableToParsePathCalls());

        self::assertEquals(1, $handler->noResultsCalls());

        self::assertEquals(0, $handler->contentRetrievedCalls());

        self::assertNull($handler->collection());
    }

    /**
     * @throws Throwable
     */
    public function testFinderNoResults() : void
    {
        $finder = $this->createMock(Finder::class);

        $finder->expects(self::at(0))
            ->method('in')
            ->willReturn($finder);

        $finder->method('depth')->willReturn($finder);

        $finder->method('depth')->willReturn($finder);

        $finder->method('sortByName')->willReturn($finder);

        $finder->method('hasResults')->willReturn(false);

        $finderFactory = $this->createMock(SymfonyFinderFactory::class);

        $finderFactory->expects(self::once())
            ->method('createFinder')
            ->willReturn($finder);

        $getContentFromPath = $this->createMock(GetContentFromPath::class);

        $getContentFromFile = $this->createMock(GetContentFromFile::class);

        /** @noinspection PhpParamsInspection */
        $service = new GetContentPathCollection(
            $finderFactory,
            $getContentFromPath,
            $getContentFromFile
        );

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentPathCollection/ContentDirectory',
            $handler
        );

        self::assertEquals(0, $handler->unableToParsePathCalls());

        self::assertEquals(1, $handler->noResultsCalls());

        self::assertEquals(0, $handler->contentRetrievedCalls());

        self::assertNull($handler->collection());
    }

    /**
     * @throws Throwable
     */
    public function test() : void
    {
        $service = Di::diContainer()->get(GetContentPathCollection::class);

        $handler = $this->getDelegate();

        $service->get(
            TESTS_BASE_PATH . '/Services/GetContentPathCollection/ContentDirectory',
            $handler
        );

        self::assertEquals(0, $handler->unableToParsePathCalls());

        self::assertEquals(0, $handler->noResultsCalls());

        self::assertEquals(1, $handler->contentRetrievedCalls());

        self::assertEquals(3, $handler->collection()->count());
    }
}
