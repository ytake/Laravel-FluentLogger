<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use LogicException;
use Monolog\Logger;
use Ytake\LaravelFluent\FluentHandler;

use function unserialize;

final class FluentHandlerTest extends TestCase
{
    /** @var FluentHandler */
    private $handler;

    /** @var Filesystem */
    private $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem();
        $this->handler = new FluentHandler(
            new StubLogger($this->filesystem)
        );
    }

    public function testGetLoggerInstance(): void
    {
        $this->assertInstanceOf(StubLogger::class, $this->handler->getLogger());
    }

    /**
     * @throws FileNotFoundException
     */
    public function testLogHandler(): void
    {
        $this->handler->handle([
            'message' => 'testing',
            'level' => Logger::DEBUG,
            'extra' => [],
            'channel' => 'testing',
            'level_name' => 'testing',
            'context' => ['testing'],
        ]);
        $this->assertFileExists(__DIR__ . '/tmp/put.log');
        $array = unserialize(
            $this->filesystem->get(__DIR__ . '/tmp/put.log')
        );
        $this->assertSame('testing.testing', $array[0]);
    }

    public function testShouldThrowExceptionForMissingTag(): void
    {
        $this->expectException(LogicException::class);
        $handler = new FluentHandler(
            new StubLogger($this->filesystem),
            '{{channel}}.{{level_name}}.{{testing}}'
        );
        $handler->handle([
            'message' => 'testing',
            'level' => Logger::DEBUG,
            'extra' => [],
            'channel' => 'testing',
            'level_name' => 'testing',
            'context' => ['testing'],
        ]);
    }

    /**
     * @throws FileNotFoundException
     */
    public function testShouldReturnContextExceptionAsString(): void
    {
        $this->handler->handle([
            'message' => 'testing',
            'level' => Logger::DEBUG,
            'extra' => [],
            'channel' => 'testing',
            'level_name' => 'testing',
            'context' => [
                'testing' => 'tests',
                'exception' => new Exception('something wrong'),
            ],
        ]);
        $this->assertFileExists(__DIR__ . '/tmp/put.log');
        $array = unserialize(
            $this->filesystem->get(__DIR__ . '/tmp/put.log')
        );
        $this->assertMatchesRegularExpression(
            '/FluentHandlerTest->testShouldReturnContextExceptionAsString/i',
            $array[1]['context']
        );
    }

    protected function tearDown(): void
    {
        $this->filesystem->delete(__DIR__ . '/tmp/put.log');
    }
}
