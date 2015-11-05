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

use Fluent\Logger\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class FluentHandler
 *
 * @package Ytake\LaravelFluent
 */
class FluentHandler extends AbstractProcessingHandler
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var string  */
    protected $tagFormat = '%s.%s';

    /**
     * FluentHandler constructor.
     *
     * @param LoggerInterface $logger
     * @param bool|int        $level
     * @param bool|true       $bubble
     */
    public function __construct(LoggerInterface $logger, $level = Logger::DEBUG, $bubble = true)
    {
        $this->logger = $logger;
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $this->logger->post(
            sprintf($this->tagFormat, $record['channel'], $record['level_name']),
            [$record['message'] => $record['context']]
        );
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
