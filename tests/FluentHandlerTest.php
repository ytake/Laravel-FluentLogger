<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use LogicException;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Ytake\LaravelFluent\FluentHandler;

use function unserialize;

final class FluentHandlerTest extends TestCase
{
    private FluentHandler $handler;
    private Filesystem $filesystem;

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
        $log = new LogRecord(
            datetime:  new \DateTimeImmutable(),
            channel: 'testing',
            level: Level::Debug,
            message: 'our log message',
            context: ['log context'],
            extra: []
        );
        $this->handler->handle($log);
        $this->assertFileExists(__DIR__ . '/tmp/put.log');
        $array = unserialize(
            $this->filesystem->get(__DIR__ . '/tmp/put.log')
        );
        $this->assertSame('testing.DEBUG', $array[0]);
    }


    public function testShouldThrowExceptionForMissingTag(): void
    {
        $this->expectException(LogicException::class);
        $handler = new FluentHandler(
            new StubLogger($this->filesystem),
            '{{channel}}.{{level_name}}.{{testing}}'
        );

        $log = new LogRecord(
            datetime:  new \DateTimeImmutable(),
            channel: 'testing',
            level: Level::Debug,
            message: 'our log message',
            context: ['log context'],
            extra: []
        );
        $handler->handle($log);
    }

    public function testShouldBeOutputInSpecifiedFormat(): void
    {
        $handler = new FluentHandler(
            new StubLogger($this->filesystem),
            '{{channel}}.{{level_name}}.{{testing}}.{{foo}}'
        );

        $log = new LogRecord(
            datetime:  new \DateTimeImmutable(),
            channel: 'testing',
            level: Level::Debug,
            message: 'our log message',
            context: ['log context'],
            extra: [
                'testing' => 'logger',
                'foo' => 'bar',
            ]
        );
        $handler->handle($log);
        $this->assertFileExists(__DIR__ . '/tmp/put.log');
        $array = unserialize(
            $this->filesystem->get(__DIR__ . '/tmp/put.log')
        );
        $this->assertSame('testing.DEBUG.logger.bar', $array[0]);
    }

    /**
     * @throws FileNotFoundException
     */
    public function testShouldReturnContextExceptionAsString(): void
    {
        $log = new LogRecord(
            datetime:  new \DateTimeImmutable(),
            channel: 'testing',
            level: Level::Debug,
            message: 'our log message',
            context: [
                'testing' => 'tests',
                'exception' => new Exception('something wrong'),
            ],
            extra: []
        );
        $this->handler->handle($log);
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
