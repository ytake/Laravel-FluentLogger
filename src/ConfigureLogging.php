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
 * Copyright (c) 2015 Yuuki Takezawa
 */

namespace Ytake\LaravelFluent;

use Fluent\Logger\FluentLogger;
use Monolog\Logger;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\ConfigureLogging as BootstrapConfigureLogging;

/**
 * Class ConfigureLogging
 *
 * @package Ytake\LaravelFluent
 */
class ConfigureLogging extends BootstrapConfigureLogging
{
    /**
     * {@inheritdoc}
     */
    protected function registerLogger(Application $app)
    {
        $app->instance('log', $log = new Writer(
            new Logger($app->environment()), $app['events'])
        );

        return $log;
    }

    /**
     * pushHandler Fluentd
     * @param Application $app
     * @param Writer      $log
     */
    protected function configureFluentHandler(Application $app, Writer $log)
    {
        $configure = $app['config']->get('fluent');
        $host = $configure['host'] ? $configure['host'] : FluentLogger::DEFAULT_ADDRESS;
        $port = $configure['port'] ? $configure['port'] : FluentLogger::DEFAULT_LISTEN_PORT;
        $options = $configure['options'] ? $configure['options'] : [];
        $tagFormat = $configure['tagFormat'] ? $configure['tagFormat'] : null;
        $log->useFluentLogger($host, $port, $options, $tagFormat);
    }
}
