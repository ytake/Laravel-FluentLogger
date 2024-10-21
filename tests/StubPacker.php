<?php

declare(strict_types=1);

namespace Tests;

use Fluent\Logger\Entity;
use Fluent\Logger\PackerInterface;

use function serialize;

final class StubPacker implements PackerInterface
{
    public function pack(Entity $entity): string
    {
        return serialize([$entity->getTag(), $entity->getTime(), $entity->getData()]);
    }
}
