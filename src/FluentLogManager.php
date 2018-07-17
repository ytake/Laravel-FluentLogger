<?php
declare(strict_types=1);

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 *
 * Copyright (c) 2015-2018 Yuuki Takezawa
 *
 */

namespace Ytake\LaravelFluent;

use Fluent\Logger\FluentLogger;
use Fluent\Logger\PackerInterface;
use Illuminate\Log\LogManager;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;

use function is_null;
use function class_exists;
use function strval;

/**
 * Class FluentLogManager
 */
final class FluentLogManager extends LogManager
{
    /** @var \Illuminate\Contracts\Container\Container */
    protected $app;

    /**
     * @param array $config
     *
     * @return LoggerInterface
     */
    protected function createFluentDriver(array $config): LoggerInterface
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(
                $this->createFluentHandler($config)
            ),
        ]);
    }

    /**
     * @param array $config
     *
     * @return HandlerInterface
     */
    private function createFluentHandler(array $config): HandlerInterface
    {
        $configure = $this->app['config']['fluent'];
        $fluentHandler = $this->detectHandler($configure);
        return new $fluentHandler(
            new FluentLogger(
                $configure['host'] ?? FluentLogger::DEFAULT_ADDRESS,
                (int)$configure['port'] ?? FluentLogger::DEFAULT_LISTEN_PORT,
                $configure['options'] ?? [],
                $this->detectPacker($configure)
            ),
            $configure['tagFormat'] ?? null,
            $this->level($config)
        );
    }

    /**
     * @param array $config
     *
     * @return LoggerInterface
     */
    public function __invoke(array $config): LoggerInterface
    {
        return $this->createFluentDriver($config);
    }

    /**
     * @return string
     */
    protected function defaultHandler(): string
    {
        return FluentHandler::class;
    }

    /**
     * @param array $configure
     *
     * @return PackerInterface|null
     */
    protected function detectPacker(array $configure): ?PackerInterface
    {
        if (!is_null($configure['packer'])) {
            if (class_exists($configure['packer'])) {
                return $this->app->make($configure['packer']);
            }
        }
        return null;
    }

    /**
     * @param array $configure
     *
     * @return string
     */
    protected function detectHandler(array $configure): string
    {
        $handler = $configure['handler'] ?? null;
        if (!is_null($handler)) {
            if (class_exists($handler)) {
                return strval($handler);
            }
        }
        return $this->defaultHandler();
    }
}
