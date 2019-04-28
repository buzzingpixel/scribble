<?php

declare(strict_types=1);

namespace BuzzingPixel\Tests;

use BuzzingPixel\Scribble\ScribbleApi;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFile;
use BuzzingPixel\Scribble\Services\GetContentFromFile\GetContentFromFileDelegate;
use BuzzingPixel\Scribble\Services\GetContentFromFile\SplFileInfo;
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
}
