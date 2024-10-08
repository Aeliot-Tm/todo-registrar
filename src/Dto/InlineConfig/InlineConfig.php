<?php

declare(strict_types=1);

/*
 * This file is part of the TODO Registrar project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\TodoRegistrar\Dto\InlineConfig;

use Aeliot\TodoRegistrar\InlineConfigInterface;

class InlineConfig implements InlineConfigInterface
{
    /**
     * @param array<array-key,mixed> $data
     */
    public function __construct(
        private array $data,
    ) {
    }

    /**
     * @param int|string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->data);
    }

    /**
     * @param int|string $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset];
    }

    /**
     * @param int|string $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException('Setting value is not allowed.');
    }

    /**
     * @param int|string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException('Unsetting value is not allowed.');
    }
}
