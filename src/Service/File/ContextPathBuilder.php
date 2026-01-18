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

namespace Aeliot\TodoRegistrar\Service\File;

use Aeliot\TodoRegistrarContracts\ContextNodeInterface;

/**
 * Builds context path string in code block format.
 *
 * @internal
 */
final readonly class ContextPathBuilder
{
    /**
     * @param list<ContextNodeInterface> $nodes
     */
    public function build(array $nodes): string
    {
        $lines = [];

        foreach ($nodes as $node) {
            $lines[] = match ($node->getKind()) {
                ContextNodeInterface::KIND_FILE => "File: {$node->getName()}",
                ContextNodeInterface::KIND_NAMESPACE => "Namespace: {$node->getName()}",
                ContextNodeInterface::KIND_CLASS => 'Class: ' . ($node->getName() ?? '{anonymous}'),
                ContextNodeInterface::KIND_INTERFACE => "Interface: {$node->getName()}",
                ContextNodeInterface::KIND_TRAIT => "Trait: {$node->getName()}",
                ContextNodeInterface::KIND_ENUM => "Enum: {$node->getName()}",
                ContextNodeInterface::KIND_METHOD => "Method: {$node->getName()}()",
                ContextNodeInterface::KIND_FUNCTION => "Function: {$node->getName()}()",
                ContextNodeInterface::KIND_CLOSURE => 'Closure',
                ContextNodeInterface::KIND_ARROW_FUNCTION => 'Arrow function',
                ContextNodeInterface::KIND_MATCH => 'Match expression',
                default => ucfirst($node->getKind()) . ($node->getName() ? ": {$node->getName()}" : ''),
            };
        }

        return "```\n" . implode("\n", $lines) . "\n```";
    }
}
