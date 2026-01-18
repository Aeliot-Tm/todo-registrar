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

/**
 * Lazy context for comment position.
 *
 * @internal
 */
final readonly class MappedContext implements ContextInterface
{
    /**
     * @param array<int, list<ContextNode>>|\ArrayAccess<int, list<ContextNode>> $contextMap
     */
    public function __construct(
        private int $line,
        private array|\ArrayAccess $contextMap,
    ) {
    }

    public function getContextNodes(): array
    {
        return $this->contextMap[$this->line] ?? [];
    }
}
