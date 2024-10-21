<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

use function assert;

class TestCase extends PHPUnitTestCase
{
    /** @var Container  */
    protected $app;

    /**
     * @throws FileNotFoundException
     */
    protected function setUp(): void
    {
        $this->app = $this->createApplicationContainer();
    }

    /**
     * @throws FileNotFoundException
     */
    protected function createApplicationContainer(): Container
    {
        $container = $this->getExtendedContainer();
        assert($container instanceof Container || $container instanceof Application);
        $filesystem = new Filesystem();
        $container->instance('config', new Repository());
        $container['config']
            ->set('fluent', $filesystem->getRequire(__DIR__ . '/config/fluent.php'));
        $container['config']
            ->set('logging', $filesystem->getRequire(__DIR__ . '/config/logging.php'));

        $eventProvider = new EventServiceProvider($container);
        $eventProvider->register();

        return $container;
    }

    protected function getExtendedContainer(): Container
    {
        return new class () extends Container {
            public function storagePath(): string
            {
                return __DIR__ . '/storages';
            }

            public function runningUnitTests(): bool
            {
                return true;
            }
        };
    }
}
