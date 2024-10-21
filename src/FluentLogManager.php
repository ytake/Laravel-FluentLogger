<?php

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
 * Copyright (c) 2015-2021 Yuuki Takezawa
 */

declare(strict_types=1);

namespace Ytake\LaravelFluent;

use Fluent\Logger\FluentLogger;
use Fluent\Logger\PackerInterface;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Log\LogManager;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;

use function class_exists;
use function is_null;
use function strval;

/**
 * FluentLogManager
 * @property Container $app
 */
class FluentLogManager extends LogManager
{
    /**
     * @param array<string, mixed> $config
     * @return LoggerInterface
     * @throws BindingResolutionException
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
     * @return array{
     *     host: string|null,
     *     port:int|null,
     *     options: array<string, mixed>|null,
     *     packer: string|null,
     *     handler: string|null,
     *     processors?: array<callable(\Monolog\LogRecord): \Monolog\LogRecord>|null,
     *     tagFormat: string|null
     * }
     * @throws BindingResolutionException
     */
    private function detectConfig(): array
    {
        /** @var Repository $repository */
        $repository = $this->app->make('config');

        assert($repository->has('fluent'));
        /** @var array{
         *     host: string|null,
         *     port:int|null,
         *     options: array<string, mixed>|null,
         *     packer: string|null,
         *     handler: string|null,
         *     processors?: array<callable(\Monolog\LogRecord): \Monolog\LogRecord>|null,
         *     tagFormat: string|null
         * } $config
         */
        $config = $repository->get('fluent');
        assert(is_array($config));

        return $config;
    }

    /**
     * @param array<string, mixed> $config
     * @return HandlerInterface
     * @throws BindingResolutionException
     */
    private function createFluentHandler(array $config): HandlerInterface
    {
        $configure = $this->detectConfig();
        $fluentHandler = $this->detectHandler($configure);
        $handler = new $fluentHandler(
            new FluentLogger(
                $configure['host'] ?? FluentLogger::DEFAULT_ADDRESS,
                $configure['port'] ?? FluentLogger::DEFAULT_LISTEN_PORT,
                $configure['options'] ?? [],
                $this->detectPacker($configure)
            ),
            $configure['tagFormat'] ?? null,
            $this->level($config)
        );
        assert(is_a($handler, FluentHandler::class, true));
        if (isset($configure['processors']) && is_array($configure['processors'])) {
            foreach ($configure['processors'] as $processor) {
                if (is_string($processor) && class_exists($processor)) {
                    // @var callable(\Monolog\LogRecord): \Monolog\LogRecord $processor
                    $processor = $this->app->make($processor);
                }
                // @phpstan-ignore-next-line
                $handler->pushProcessor($processor);
            }
        }
        return $handler;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return LoggerInterface
     * @throws BindingResolutionException
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
        return FluentHandler::class; // is of type class-string<FluentHandler>
    }

    /**
     * @param array{
     *     host: string|null,
     *     port:int|null,
     *     options: array<string, mixed>|null,
     *     packer: string|null,
     *     handler: string|null,
     *     processors?: array<callable(\Monolog\LogRecord): \Monolog\LogRecord>|null,
     *     tagFormat: string|null
     * } $configure
     *
     * @return PackerInterface|null
     * @throws BindingResolutionException
     */
    protected function detectPacker(array $configure): PackerInterface|null
    {
        if (!is_null($configure['packer']) && class_exists($configure['packer'])) {
            // @phpstan-ignore-next-line
            return $this->app->make($configure['packer']);
        }
        return null;
    }

    /**
     * @param array{
     *     host: string|null,
     *     port:int|null,
     *     options: array<string, mixed>|null,
     *     packer: string|null,
     *     handler: string|null,
     *     processors?: array<callable(\Monolog\LogRecord): \Monolog\LogRecord>|null,
     *     tagFormat: string|null
     * } $configure
     *
     * @return string
     */
    protected function detectHandler(array $configure): string
    {
        $handler = $configure['handler'] ?? null;
        if (!is_null($handler) && class_exists((string) $handler)) {
            return strval($handler);
        }
        return $this->defaultHandler();
    }
}
