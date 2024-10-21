<?php

declare(strict_types=1);

namespace Tests;

use Fluent\Logger\Entity;
use Fluent\Logger\LoggerInterface;
use Illuminate\Filesystem\Filesystem;

use function serialize;

final class StubLogger implements LoggerInterface
{
    public function __construct(protected Filesystem $filesystem)
    {
    }

    public function post($tag, array $data): void
    {
        $this->filesystem->put(__DIR__ . '/tmp/put.log', serialize([$tag, $data]));
    }

    /**
     * Entity $entity
     */
    public function post2(
        Entity $entity
    ): void {
        // TODO: Implement post2() method.
    }
}
