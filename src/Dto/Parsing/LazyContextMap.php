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

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;

/**
 * Provides lazy contextMap building.
 * Context map is built only on first access via ArrayAccess.
 *
 * @internal
 *
 * @implements \ArrayAccess<int, list<ContextNode>>
 */
final class LazyContextMap implements \ArrayAccess
{
    /**
     * @var array<int, list<ContextNode>>|null
     */
    private ?array $contextMap = null;

    /**
     * @param array<Stmt> $ast
     */
    public function __construct(
        private readonly array $ast,
        private readonly string $filePath,
    ) {
    }

    public function offsetExists(mixed $offset): bool
    {
        $this->contextMap ??= $this->buildContextMap();

        return isset($this->contextMap[$offset]);
    }

    /**
     * @return list<ContextNode>
     */
    public function offsetGet(mixed $offset): array
    {
        $this->contextMap ??= $this->buildContextMap();

        return $this->contextMap[$offset] ?? [];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException('LazyContextMap is read-only');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException('LazyContextMap is read-only');
    }

    /**
     * @return array<int, list<ContextNode>>
     */
    private function buildContextMap(): array
    {
        $visitor = new ContextMapVisitor($this->filePath);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($this->ast);

        return $visitor->getContextMap();
    }
}
