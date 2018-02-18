<?php
declare(strict_types=1);

use Ytake\LaravelFluent\FluentLogManager;

/**
 * Class LogManagerTest
 */
final class LogManagerTest extends TestCase
{
    /** @var FluentLogManager */
    private $logManager;

    protected function setUp()
    {
        parent::setUp();
        $this->logManager = new FluentLogManager($this->app);
    }

    public function testShouldBeLoggerInterface(): void
    {
        $this->logManager->setDefaultDriver('fluent');
        $stackLogger = $this->logManager->stack(['fluent']);
        $this->assertInstanceOf(\Illuminate\Log\Logger::class, $stackLogger);
    }

    public function testShouldBeReturnFluentLogger(): void
    {
        $this->app['config']->set('fluent.packer', stubPacker::class);
        $this->logManager->setDefaultDriver('fluent');
        /** @var \Illuminate\Log\Logger $logger */
        $logDriver = $this->logManager->driver();
        $this->assertInstanceOf(\Illuminate\Log\Logger::class, $logDriver);
        /** @var Monolog\Logger $logger */
        $logger = $logDriver->getLogger();
        $handler = $logger->getHandlers()[0];
        $this->assertInstanceOf(\Ytake\LaravelFluent\FluentHandler::class, $handler);
        /** @var \Ytake\LaravelFluent\FluentHandler $handler */
        $this->assertInstanceOf(\Fluent\Logger\FluentLogger::class, $fluent = $handler->getLogger());
        $this->assertInstanceOf(stubPacker::class, $fluent->getPacker());
    }
}

class stubPacker implements \Fluent\Logger\PackerInterface
{
    public function pack(\Fluent\Logger\Entity $entity)
    {
        return serialize([$entity->getTag(), $entity->getTime(), $entity->getData()]);
    }
}
