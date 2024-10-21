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

use Exception;
use Fluent\Logger\LoggerInterface;
use LogicException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Psr\Log\LogLevel;

use function array_key_exists;
use function preg_match_all;
use function sprintf;
use function str_replace;

/**
 * FluentHandler
 */
class FluentHandler extends AbstractProcessingHandler
{
    protected string $tagFormat = '{{channel}}.{{level_name}}';

    /**
     * @param LoggerInterface              $logger
     * @param null|string                  $tagFormat
     * @param int|string|Level|LogLevel::* $level
     * @param bool                         $bubble
     *
     * @phpstan-param value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::* $level
     */
    public function __construct(
        protected LoggerInterface $logger,
        string $tagFormat = null,
        Level|int|string $level = Level::Debug,
        bool $bubble = true
    ) {
        if ($tagFormat !== null) {
            $this->tagFormat = $tagFormat;
        }
        parent::__construct($level, $bubble);
    }

    /**
     * @param LogRecord $record
     * @return void
     */
    protected function write(LogRecord $record): void
    {
        $tag = $this->populateTag($record);
        $this->logger->post(
            $tag,
            [
                'message' => $record->message,
                // @phpstan-ignore-next-line
                'context' => $this->getContext($record->context),
                'extra'   => $record->extra,
            ]
        );
    }

    protected function populateTag(LogRecord $record): string
    {
        return $this->processFormat($record, $this->tagFormat);
    }

    protected function processFormat(LogRecord $record, string $tag): string
    {
        if (preg_match_all('/{{(.*?)}}/', $tag, $matches)) {
            foreach ($matches[1] as $match) {
                if (isset($record[$match])) {
                    $arr = $record;
                } elseif (isset($record->extra[$match])) {
                    $arr = $record->extra;
                } else {
                    throw new LogicException('No such field in the record');
                }

                $tag = str_replace(sprintf('{{%s}}', $match), $arr[$match], $tag);
            }
        }

        return $tag;
    }

    /**
     * returns the context
     *
     * @param array{exception: Exception} $context
     *
     * @return array<string, mixed>|string
     */
    protected function getContext(array $context): array|string
    {
        if ($this->contextHasException($context)) {
            return $this->getContextExceptionTrace($context);
        }

        return $context;
    }

    /**
     * Identifies the content type of the given $context
     *
     * @param  array<string, mixed> $context
     *
     * @return bool
     */
    protected function contextHasException(array $context): bool
    {
        return (
            is_array($context)
            && array_key_exists('exception', $context)
            && $context['exception'] instanceof \Throwable
        );
    }

    /**
     * Returns the entire exception trace as a string
     *
     * @param  array{'exception': Exception} $context

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
