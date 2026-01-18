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

namespace Aeliot\TodoRegistrar\Service\ContextPath\Builder;

use Aeliot\TodoRegistrarContracts\ContextNodeInterface;

abstract readonly class AbstractContextPathBuilder
{
    /**
     * @param ContextNodeInterface[] $nodes
     *
     * @return string[]
     */
    protected function getLines(array $nodes): array
    {
        $lines = [];

        foreach ($nodes as $node) {
            $lines[] = match ($node->getKind()) {
                ContextNodeInterface::KIND_ARROW_FUNCTION => 'Arrow function',
                ContextNodeInterface::KIND_CLASS => 'Class: ' . ($node->getName() ?? '{anonymous}'),
                ContextNodeInterface::KIND_CLASS_CONST => 'Constant: ' . ($node->getName() ?? '{unknown}'),
                ContextNodeInterface::KIND_CLOSURE => 'Closure',
                ContextNodeInterface::KIND_ENUM => "Enum: {$node->getName()}",
                ContextNodeInterface::KIND_ENUM_CASE => "Enum case: {$node->getName()}",
                ContextNodeInterface::KIND_FILE => "File: {$node->getName()}",
                ContextNodeInterface::KIND_FUNCTION => "Function: {$node->getName()}()",
                ContextNodeInterface::KIND_INTERFACE => "Interface: {$node->getName()}",
                ContextNodeInterface::KIND_MATCH => 'Match expression',
                ContextNodeInterface::KIND_METHOD => "Method: {$node->getName()}()",
                ContextNodeInterface::KIND_NAMESPACE => "Namespace: {$node->getName()}",
                ContextNodeInterface::KIND_PARAMETER => 'Parameter: ' . ($node->getName() ?? '{unknown}'),
                ContextNodeInterface::KIND_PROPERTY => 'Property: ' . ($node->getName() ?? '{unknown}'),
                ContextNodeInterface::KIND_TRAIT => "Trait: {$node->getName()}",
                default => ucfirst($node->getKind()) . ($node->getName() ? ": {$node->getName()}" : ''),
            };
        }

        return $lines;
    }
}
