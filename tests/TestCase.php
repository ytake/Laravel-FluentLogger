<?php
declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var \Illuminate\Container\Container  */
    protected $app;

    protected function setUp()
    {
        $this->app = $this->createApplicationContainer();
    }

    /**
     * @return Container
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createApplicationContainer(): Container
    {
        $container = $this->getExtendedContainer();
        $filesystem = new Filesystem();
        $container->instance('config', new Repository());
        $container['config']
            ->set("fluent", $filesystem->getRequire(__DIR__ . '/config/fluent.php'));
        $container['config']
            ->set("logging", $filesystem->getRequire(__DIR__ . '/config/logging.php'));

        $eventProvider = new \Illuminate\Events\EventServiceProvider($container);
        $eventProvider->register();
        return $container;
    }

    protected function getExtendedContainer(): Container
    {
        return new class() extends Container {
            public function storagePath(): string
            {
                return __DIR__ . '/storages';
            }
        };
    }
}
