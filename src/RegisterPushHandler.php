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
use Illuminate\Contracts\Logging\Log as LoggerInterface;

/**
 * Class RegisterPushHandler
 *
 * @package Ytake\LaravelFluent
 */
class RegisterPushHandler
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var array */
    protected $config;

    /**
     * @param LoggerInterface $logger
     * @param array           $config
     */
    public function __construct(LoggerInterface $logger, array $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * add handler
     */
    public function pushHandler()
    {
        $this->logger->getMonolog()->pushHandler(
            new FluentHandler(
                new FluentLogger($this->config['host'], $this->config['port'], $this->config['options']
                )
            )
        );
    }
}
