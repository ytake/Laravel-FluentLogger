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
    protected $tagFormat = '{{channel}}.{{level_name}}';

    /**
     * FluentHandler constructor.
     *
     * @param LoggerInterface $logger
     * @param bool|int        $level
     * @param bool|true       $bubble
     */
    public function __construct(LoggerInterface $logger, $tagFormat = null, $level = Logger::DEBUG, $bubble = true)
    {
        $this->logger = $logger;
        if ($tagFormat !== null) {
            $this->tagFormat = $tagFormat;
        }
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        //sprintf($this->tagFormat, $record['channel'], $record['level_name'])
        $tag = $this->populateTag($record);
        $this->logger->post(
            $tag,
            [$record['message'] => $record['context']]
        );
    }

    /**
     * @param array $record
     */
    protected function populateTag($record)
    {
        return $this->processFormat($record, $this->tagFormat);
    }

    /**
     * @param array $record
     * @param array $format
     */
    protected function processFormat($record, $format)
    {
        $tag = $format;

        if (preg_match_all('/\{\{(.*?)\}\}/', $tag, $matches)) {
            foreach ($matches[1] as $match) {
                if (!isset($record[$match])) {
                    throw new Exception('No such field in the record');
                }
                $tag = str_replace(sprintf('{{%s}}', $match), $record[$match], $tag);
            }
        }

        return $tag;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
