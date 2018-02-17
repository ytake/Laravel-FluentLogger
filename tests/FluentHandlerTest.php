<?php

class FluentHandlerTest extends \TestCase
{
    /** @var \Ytake\LaravelFluent\FluentHandler */
    protected $handler;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $filesystem;

    protected function setUp()
    {
        parent::setUp();
        $this->filesystem = new \Illuminate\Filesystem\Filesystem;
        $this->handler = new \Ytake\LaravelFluent\FluentHandler(
            new stubLogger($this->filesystem)
        );
    }

    public function testGetLoggerInstance()
    {
        $this->assertInstanceOf('stubLogger', $this->handler->getLogger());
    }

    public function testLogHandler()
    {
        $this->handler->handle([
            'message'    => 'testing',
            'level'      => \Monolog\Logger::DEBUG,
            'extra'      => [],
            'channel'    => 'testing',
            'level_name' => 'testing',
            'context'    => ['testing'],
        ]);
        $this->assertFileExists(__DIR__ . '/tmp/put.log');
        $log = $this->filesystem->get(__DIR__ . '/tmp/put.log');
        list($tag, $data) = (unserialize($log));
        $this->assertSame('testing.testing', $tag);
    }

    /**
     * @expectedException \LogicException
     */
    public function testShouldThrowExceptionForMissingTag()
    {
        $handler = new \Ytake\LaravelFluent\FluentHandler(
            new stubLogger($this->filesystem),
            '{{channel}}.{{level_name}}.{{testing}}'
        );
        $handler->handle([
            'message'    => 'testing',
            'level'      => \Monolog\Logger::DEBUG,
            'extra'      => [],
            'channel'    => 'testing',
            'level_name' => 'testing',
            'context'    => ['testing'],
        ]);
    }

    public function testShouldReturnContextExceptionAsString()
    {
        $this->handler->handle([
            'message'    => 'testing',
            'level'      => \Monolog\Logger::DEBUG,
            'extra'      => [],
            'channel'    => 'testing',
            'level_name' => 'testing',
            'context'    => [
                'testing',
                'exception' => new \Exception('something wrong'),
            ],
        ]);
        $this->assertFileExists(__DIR__ . '/tmp/put.log');
        $log = $this->filesystem->get(__DIR__ . '/tmp/put.log');
        list($_, $data) = (unserialize($log));
        $this->assertRegExp(
            "/FluentHandlerTest->testShouldReturnContextExceptionAsString/i",
            $data['context']
        );
    }

    protected function tearDown()
    {
        $this->filesystem->delete(__DIR__ . '/tmp/put.log');
    }
}

class stubLogger implements \Fluent\Logger\LoggerInterface
{
    /** @var \Illuminate\Filesystem\Filesystem */
    protected $filesystem;

    public function __construct(\Illuminate\Filesystem\Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function post($tag, array $data)
    {
        $this->filesystem->put(__DIR__ . '/tmp/put.log', serialize([$tag, $data]));
    }

    public function post2(\Fluent\Logger\Entity $entity)
    {
        // TODO: Implement post2() method.
    }
}
