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
 * Copyright (c) 2015-2017 Yuuki Takezawa
 *
 */

namespace Ytake\LaravelFluent;

use Fluent\Logger\FluentLogger;
use Monolog\Logger as Monolog;

/**
 * Class LogServiceProvider
 */
class LogServiceProvider extends \Illuminate\Log\LogServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        /**
         * for package configure
         */
        $configPath = __DIR__ . '/config/fluent.php';
        $this->mergeConfigFrom($configPath, 'fluent');
        $this->publishes([$configPath => config_path('fluent.php')], 'log');
        parent::register();
    }

    /**
     * {@inheritdoc}
     */
    public function createLogger()
    {
        $log = new Writer(new Monolog($this->channel()), $this->app['events']);
        if ($this->app->hasMonologConfigurator()) {
            call_user_func($this->app->getMonologConfigurator(), $log->getMonolog());

            return $log;
        }
        $this->configureHandler($log);

        return $log;
    }

    /**
     * @param Writer $log
     */
    protected function configureFlunetHandler(Writer $log)
    {
        $configure = $this->app['config']->get('fluent');
        $host = $configure['host'] ? $configure['host'] : FluentLogger::DEFAULT_ADDRESS;
        $port = $configure['port'] ? $configure['port'] : FluentLogger::DEFAULT_LISTEN_PORT;
        $options = $configure['options'] ? $configure['options'] : [];
        $tagFormat = isset($configure['tagFormat']) ? $configure['tagFormat'] : null;
        $log->useFluentLogger($host, $port, $options, $tagFormat);
    }
}
