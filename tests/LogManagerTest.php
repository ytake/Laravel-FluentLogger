<?php

declare(strict_types=1);

namespace Tests;

use Fluent\Logger\FluentLogger;
use Illuminate\Log\Logger;
use Ytake\LaravelFluent\FluentHandler;
use Ytake\LaravelFluent\FluentLogManager;

use function assert;

final class LogManagerTest extends TestCase
{
    /** @var FluentLogManager */
    private $logManager;

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
}
