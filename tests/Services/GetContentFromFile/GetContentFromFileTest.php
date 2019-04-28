<?php

declare(strict_types=1);

namespace BuzzingPixel\Tests\Services\GetContentFromFile;

use BuzzingPixel\Scribble\Services\GetContentFromFile\Content;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromFile\SplFileInfo;
use corbomite\di\Di;
use LogicException;
use PHPUnit\Framework\TestCase;
use Throwable;

class GetContentFromFileTest extends TestCase
{
    private function getDelegate()
    {
        return new class implements GetContentFromFileDelegate {
            /** @var int */
            private $unableToParseCalls = 0;

            /** @var int */
            private $contentRetrievedCalls = 0;

            /** @var Content|null */
            private $content;

            public function getUnableToParseCalls() : int
            {
                return $this->unableToParseCalls;
            }

            public function getContentRetrievedCalls() : int
            {
                return $this->contentRetrievedCalls;
            }

            public function getContent() : ?Content
            {
                return $this->content;
            }

            public function unableToParseFile() : void
            {
                $this->unableToParseCalls++;
            }

            public function contentRetrieved(Content $content) : void
            {
                $this->contentRetrievedCalls++;
                $this->content = $content;
            }
        };
    }

    /**
     * @throws Throwable
     */
    public function testInvalidFile() : void
    {
        $handler = $this->getDelegate();

        $file = new SplFileInfo(TESTS_BASE_PATH . '/Services/GetContentFromFile/asdf.md');

        $service = Di::diContainer()->get(GetContentFromFile::class);

        $service->get($file, $handler);

        self::assertEquals(1, $handler->getUnableToParseCalls());

        self::assertEquals(0, $handler->getContentRetrievedCalls());

        self::assertNull($handler->getContent());
    }

    /**
     * @throws Throwable
     */
    public function testValidFile() : void
    {
        $handler = $this->getDelegate();

        $file = new SplFileInfo(TESTS_BASE_PATH . '/Services/GetContentFromFile/TestContentFile.md');

        $service = Di::diContainer()->get(GetContentFromFile::class);

        $service->get($file, $handler);

        self::assertEquals(0, $handler->getUnableToParseCalls());

        self::assertEquals(1, $handler->getContentRetrievedCalls());

        /** @var Content $content */
        $content = $handler->getContent();

        self::assertInstanceOf(Content::class, $content);

        self::assertEquals(
            "\nTesting paragraph 1\n\nTesting paragraph 2\n",
            $content->markdown()
        );

        self::assertEquals(
            "<p>Testing paragraph 1</p>\n<p>Testing paragraph 2</p>\n",
            $content->html()
        );

        self::assertEquals(
            [
                'asdf' => 'thing',
                'foo' => ['bar' => 'thing'],
            ],
            $content->meta()
        );

        self::assertNull($content->getMetaItem('bar'));

        self::assertNull($content->getMetaItem('asdf.foo'));

        self::assertEquals(
            ['bar' => 'thing'],
            $content->getMetaItem('foo')
        );

        self::assertEquals(
            'thing',
            $content->getMetaItem('foo.bar')
        );

        self::assertNull($content->getMetaItem('foo.bar.asdf'));

        $exception = null;

        try {
            $content->__construct(
                'test',
                'thing',
                []
            );
        } catch (LogicException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(LogicException::class, $exception);

        self::assertEquals(
            'Instance may only be instantiated once',
            $exception->getMessage()
        );
    }
}
