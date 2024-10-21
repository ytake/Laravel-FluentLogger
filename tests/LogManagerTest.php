<?php

declare(strict_types=1);

namespace Tests;

use Fluent\Logger\FluentLogger;
use Illuminate\Log\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Ytake\LaravelFluent\FluentHandler;
use Ytake\LaravelFluent\FluentLogManager;

use function assert;

final class LogManagerTest extends TestCase
{
    private FluentLogManager $logManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logManager = new FluentLogManager($this->app);
    }

    public function testShouldBeLoggerInterface(): void
    {
        $this->logManager->setDefaultDriver('fluent');
        $stackLogger = $this->logManager->stack(['fluent']);
        $this->assertInstanceOf(Logger::class, $stackLogger);
    }

    public function testShouldBeReturnFluentLogger(): void
    {
        $this->app['config']->set('fluent.packer', StubPacker::class);
        $this->logManager->setDefaultDriver('fluent');
        /** @var Logger logDriver */
        $logDriver = $this->logManager->driver();
        $this->assertInstanceOf(Logger::class, $logDriver);
        $logger = $logDriver->getLogger();
        assert($logger instanceof \Monolog\Logger);
        $handler = $logger->getHandlers()[0];
        $this->assertInstanceOf(FluentHandler::class, $handler);
        /** @var FluentHandler $handler */
        $this->assertInstanceOf(FluentLogger::class, $fluent = $handler->getLogger());
        $this->assertInstanceOf(StubPacker::class, $fluent->getPacker());
    }

    /**
     * This should be valid:
     *
     * 'processors' => [new Monolog\Processor\MemoryUsageProcessor()],
     */
    public function testAddObjectAsProcessor(): void
    {
        $processor = new MemoryUsageProcessor();

        $this->app['config']->set('fluent.processors', [$processor]);
        $this->logManager->setDefaultDriver('fluent');

        /** @var \Illuminate\Log\Logger $logger */
        $logDriver = $this->logManager->driver();

        /** @var \Ytake\LaravelFluent\FluentHandler::class $logger */
        $logger = $logDriver->getLogger()->getHandlers()[0];
        $actualProcessor = $logger->popProcessor();

        $this->assertEquals($processor, $actualProcessor);
    }

    /**
     * This should be valid:
     *
     * 'processors' => [Monolog\Processor\MemoryUsageProcessor::class],
     */
    public function testAddStringAsProcessor(): void
    {
        $processor = MemoryUsageProcessor::class;

        $this->app['config']->set('fluent.processors', [$processor]);
        $this->logManager->setDefaultDriver('fluent');

        /** @var \Illuminate\Log\Logger $logger */
        $logDriver = $this->logManager->driver();

        /** @var \Ytake\LaravelFluent\FluentHandler::class $logger */
        $logger = $logDriver->getLogger()->getHandlers()[0];
        $actualProcessor = $logger->popProcessor();

        $this->assertInstanceOf(MemoryUsageProcessor::class, $actualProcessor);
    }
}
