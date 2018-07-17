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
use Illuminate\Log\LogManager;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;

/**
 * Class FluentLogManager
 */
final class FluentLogManager extends LogManager
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
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

    private function createFluentHandler(array $config) : HandlerInterface
    {
        $configure = $this->app['config']['fluent'];

        $packer = null;
        if (!is_null($configure['packer'])) {
            if (class_exists($configure['packer'])) {
                $packer = $this->app->make($configure['packer']);
            }
        }

        $fluentHandler = FluentHandler::class;
        if (!is_null($configure['handler'])) {
            if (class_exists($configure['handler'])) {
                $fluentHandler = $configure['handler'];
            }
        }


        return new $fluentHandler(
            new FluentLogger(
                $configure['host'] ?? FluentLogger::DEFAULT_ADDRESS,
                (int)$configure['port'] ?? FluentLogger::DEFAULT_LISTEN_PORT,
                $configure['options'] ?? [],
                $packer
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
}
