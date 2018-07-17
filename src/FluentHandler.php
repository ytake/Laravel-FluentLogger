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

use Fluent\Logger\LoggerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

use function str_replace;
use function preg_match_all;
use function sprintf;
use function is_array;
use function array_key_exists;

/**
 * Class FluentHandler
 */
class FluentHandler extends AbstractProcessingHandler
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $tagFormat = '{{channel}}.{{level_name}}';

    /**
     * FluentHandler constructor.
     *
     * @param LoggerInterface $logger
     * @param null|string     $tagFormat
     * @param int             $level
     * @param bool            $bubble
     */
    public function __construct(
        LoggerInterface $logger,
        string $tagFormat = null,
        int $level = Logger::DEBUG,
        bool $bubble = true
    ) {
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
        $tag = $this->populateTag($record);
        $this->logger->post(
            $tag,
            [
                'message' => $record['message'],
                'context' => $this->getContext($record['context']),
                'extra'   => $record['extra'],
            ]
        );
    }

    /**
     * @param array $record
     *
     * @return string
     */
    protected function populateTag(array $record): string
    {
        return $this->processFormat($record, $this->tagFormat);
    }

    /**
     * @param array  $record
     * @param string $tag
     *
     * @return string
     */
    protected function processFormat(array $record, string $tag): string
    {
        if (preg_match_all('/\{\{(.*?)\}\}/', $tag, $matches)) {
            foreach ($matches[1] as $match) {
                if (!isset($record[$match])) {
                    throw new \LogicException('No such field in the record');
                }
                $tag = str_replace(sprintf('{{%s}}', $match), $record[$match], $tag);
            }
        }

        return $tag;
    }

    /**
     * returns the context
     *
     * @param mixed $context
     *
     * @return array|string
     */
    protected function getContext($context)
    {
        if ($this->contextHasException($context)) {
            return $this->getContextExceptionTrace($context);
        }

        return $context;
    }

    /**
     * Identifies the content type of the given $context
     *
     * @param  mixed $context
     *
     * @return bool
     */
    protected function contextHasException($context): bool
    {
        return (
            is_array($context)
            && array_key_exists('exception', $context)
            && $context['exception'] instanceof \Exception
        );
    }

    /**
     * Returns the entire exception trace as a string
     *
     * @param  array $context
     *
     * @return string
     */
    protected function getContextExceptionTrace(array $context): string
    {
        return $context['exception']->getTraceAsString();
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
