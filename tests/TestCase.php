<?php

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var \Illuminate\Container\Container  */
    protected $app;

    protected function setUp()
    {
        $this->app = $this->createApplicationContainer();
    }

    /**
     * @return \Illuminate\Container\Container
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createApplicationContainer()
    {
        $container = new \Illuminate\Container\Container;
        $filesystem = new \Illuminate\Filesystem\Filesystem;
        $container->instance('config', new \Illuminate\Config\Repository);
        $container->config->set("fluent", $filesystem->getRequire(__DIR__ . '/config/fluent.php'));

        return $container;
    }
}
