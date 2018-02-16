<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: christopherfuchs
 * Date: 14.02.18
 * Time: 15:45
 */

namespace Ytake\LaravelFluent;

use Fluent\Logger\FluentLogger;
use Illuminate\Foundation\Application;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

class CreateFluentLogger
{
    /**
     * @var Application
     */
    private $app;

    /**
     * The Log levels.
     *
     * @var array
     */
    protected $levels = [
        'debug' => Logger::DEBUG,
        'info' => Logger::INFO,
        'notice' => Logger::NOTICE,
        'warning' => Logger::WARNING,
        'error' => Logger::ERROR,
        'critical' => Logger::CRITICAL,
        'alert' => Logger::ALERT,
        'emergency' => Logger::EMERGENCY,
    ];

    /**
     * CreateFluentLogger constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Create a custom Monolog instance.
     *
     * @param array $config
     *
     * @return \Monolog\Logger
     */
    public function __invoke(array $config) : Logger
    {
        $configure = $this->app['config']->get('fluent');

        if ($configure['packer'] !== null) {
            if (class_exists($configure['packer'])) {
                $packer = $this->app->make($configure['packer']);
            }
        }

        $fluentLogger = new FluentLogger(
        $configure['host'] ?? FluentLogger::DEFAULT_ADDRESS,
        (int) $configure['port'] ?? FluentLogger::DEFAULT_LISTEN_PORT,
        $configure['options'] ?? [],
        $packer ?? null
        );

        $logger = new Logger($config["channel"], [
            $this->prepareHandler(new FluentHandler(
            $fluentLogger,
            $configure['tagFormat'] ?? null,
            $this->level($this->app['config']['app.log_level']
            ))),
        ]);

        return $logger;
    }

    /**
     * Prepare the handler for usage by Monolog.
     *
     * @param \Monolog\Handler\HandlerInterface $handler
     *
     * @return \Monolog\Handler\HandlerInterface
     */
    protected function prepareHandler(HandlerInterface $handler): HandlerInterface
    {
        return $handler->setFormatter($this->formatter());
    }

    /**
     * Get a Monolog formatter instance.
     *
     * @return \Monolog\Formatter\FormatterInterface
     */
    protected function formatter() : FormatterInterface
    {
        return tap(new LineFormatter(null, null, true, true), function ($formatter) {
            $formatter->includeStacktraces();
        });
    }

    /**
     * Parse the string level into a Monolog constant.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    protected function level(string $level) : int
    {
        $level = $level ?? 'debug';

        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        throw new \InvalidArgumentException('Invalid log level.');
    }
}
