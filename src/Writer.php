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
use Fluent\Logger\PackerInterface;

/**
 * Class Writer
 */
class Writer extends \Illuminate\Log\Writer
{
    /** @var null */
    protected $packer = null;

    /**
     * @param string $host
     * @param string $port
     * @param array  $options
     * @param null   $tagFormat
     * @param string $level
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function useFluentLogger($host, $port, $options = [], $tagFormat = null, $level = 'debug')
    {
        return $this->monolog->pushHandler(
            new FluentHandler(
                new FluentLogger($host, $port, $options, $this->packer),
                $tagFormat,
                $this->parseLevel($level)
            )
        );
    }

    /**
     * @param PackerInterface $packer
     *
     * @return $this
     */
    public function setPacker(PackerInterface $packer): self
    {
        $this->packer = $packer;

        return $this;
    }
}
