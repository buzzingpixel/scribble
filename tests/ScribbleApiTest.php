<?php

declare(strict_types=1);

namespace BuzzingPixel\Tests;

use BuzzingPixel\Scribble\ScribbleApi;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromFile\SplFileInfo;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPath;
use BuzzingPixel\Scribble\Services\GetContentFromPath\GetContentFromPathDelegate;
use BuzzingPixel\Scribble\Services\GetContentPathCollection\GetContentPathCollection;
use BuzzingPixel\Scribble\Services\GetContentPathCollection\GetContentPathCollectionDelegate;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Throwable;

class ScribbleApiTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testGetContentFromFile() : void
    {
        $vars                      = new stdClass();
        $vars->incomingSplFileInfo = null;
        $vars->incomingHandler     = null;

        $getContentFromFile = $this->createMock(GetContentFromFile::class);

        $getContentFromFile->expects(self::once())
            ->method('get')
            ->willReturnCallback(static function (
                SplFileInfo $splFileInfo,
                GetContentFromFileDelegate $handler
            ) use ($vars) : void {
                $vars->incomingSplFileInfo = $splFileInfo;
                $vars->incomingHandler     = $handler;
            });

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(GetContentFromFile::class))
            ->willReturn($getContentFromFile);

        $handler = $this->createMock(GetContentFromFileDelegate::class);

        /** @noinspection PhpParamsInspection */
        $api = new ScribbleApi($di);

        /** @noinspection PhpParamsInspection */
        $api->getContentFromFile('testFilePath', $handler);

        self::assertInstanceOf(
            SplFileInfo::class,
            $vars->incomingSplFileInfo
        );

        self::assertEquals(
            'testFilePath',
            $vars->incomingSplFileInfo->getPathname()
        );

        self::assertSame($handler, $vars->incomingHandler);
    }

    /**
     * @throws Throwable
     */
    public function testGetContentFromPath() : void
    {
        $handler = $this->createMock(GetContentFromPathDelegate::class);

        $getContentFromPath = $this->createMock(GetContentFromPath::class);

        $getContentFromPath->expects(self::once())
            ->method('get')
            ->with(
                self::equalTo('testDirInput'),
                self::equalTo($handler),
                self::equalTo(['test', 'thing'])
            );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(GetContentFromPath::class))
            ->willReturn($getContentFromPath);

        /** @noinspection PhpParamsInspection */
        $api = new ScribbleApi($di);

        /** @noinspection PhpParamsInspection */
        $api->getContentFromPath('testDirInput', $handler, ['test', 'thing']);
    }

    /**
     * @throws Throwable
     */
    public function testGetContentPathCollection() : void
    {
        $handler = $this->createMock(GetContentPathCollectionDelegate::class);

        $getContentPathCollection = $this->createMock(GetContentPathCollection::class);

        $getContentPathCollection->expects(self::once())
            ->method('get')
            ->with(
                self::equalTo('testDirInput'),
                self::equalTo($handler),
                self::equalTo(['test', 'thing'])
            );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(GetContentPathCollection::class))
            ->willReturn($getContentPathCollection);

        /** @noinspection PhpParamsInspection */
        $api = new ScribbleApi($di);

        /** @noinspection PhpParamsInspection */
        $api->getContentPathCollection('testDirInput', $handler, ['test', 'thing']);
    }
}
