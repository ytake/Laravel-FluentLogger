<?php

class WriterTest extends \TestCase
{
    /** @var \Ytake\LaravelFluent\Writer */
    protected $writer;

    protected function setUp()
    {
        parent::setUp();
        $this->writer = new \Ytake\LaravelFluent\Writer(
            new \Monolog\Logger('testing')
        );
    }

    public function testInstance()
    {
        $this->assertInstanceOf('Ytake\LaravelFluent\Writer', $this->writer);
    }

    public function testCreateFluentLogger()
    {
        $config = $this->app['config']->get('fluent');
        $logger = $this->writer->useFluentLogger($config['host'], $config['port']);
        $this->assertInstanceOf('Monolog\Logger', $logger);
    }

    public function testSetPacker()
    {
        $config = $this->app['config']->get('fluent');
        $this->writer->setPacker(new stubPacker);
        $logger = $this->writer->useFluentLogger($config['host'], $config['port']);
        $this->assertInstanceOf('Monolog\Logger', $logger);
    }
}

class stubPacker implements \Fluent\Logger\PackerInterface
{
    public function pack(\Fluent\Logger\Entity $entity)
    {
        return serialize([$entity->getTag(), $entity->getTime(), $entity->getData()]);
    }
}