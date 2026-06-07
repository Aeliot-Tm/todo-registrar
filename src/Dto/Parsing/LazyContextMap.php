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

namespace Aeliot\TodoRegistrar\Dto\Parsing;

use Aeliot\TodoRegistrar\Exception\BadMethodCallException;
use Aeliot\TodoRegistrarContracts\Context\ContextNodeInterface;

/**
 * Provides lazy contextMap building.
 * Context map is built only on first access via ArrayAccess.
 *
 * @internal
 */
final class LazyContextMap implements ContextMapInterface
{
    /**
     * @var array<int, list<ContextNodeInterface>>|null
     */
    private ?array $contextMap = null;

    public function __construct(
        private readonly ContextMapBuilderInterface $contextMapBuilder,
    ) {
    }

    public function offsetExists(mixed $offset): bool
    {
        $this->contextMap ??= $this->contextMapBuilder->buildContextMap();

        return isset($this->contextMap[$offset]);
    }

    /**
     * @return list<ContextNodeInterface>
     */
    public function offsetGet(mixed $offset): array
    {
        $this->contextMap ??= $this->contextMapBuilder->buildContextMap();

        return $this->contextMap[$offset] ?? [];
    }

    /**
     * @throws BadMethodCallException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('LazyContextMap is read-only');
    }

    /**
     * @throws BadMethodCallException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('LazyContextMap is read-only');
    }
}
