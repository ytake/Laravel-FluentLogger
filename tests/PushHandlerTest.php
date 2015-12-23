<?php

class PushHandlerTest extends \TestCase
{
    /** @var \Ytake\LaravelFluent\RegisterPushHandler */
    protected $register;

    /** @var \Monolog\Logger */
    protected $logger;

    protected function setUp()
    {
        parent::setUp();
        $this->filesystem = new \Illuminate\Filesystem\Filesystem;

        $this->logger = new \Illuminate\Log\Writer(new \Monolog\Logger('testing'));
        $this->register = new \Ytake\LaravelFluent\RegisterPushHandler(
            $this->logger,
            $this->app['config']->get('fluent')
        );
    }

    public function testPushHandler()
    {
        $this->register->pushHandler();
        $this->assertNotCount(0 , $this->logger->getMonolog()->getHandlers());
    }
}
