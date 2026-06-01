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

use Aeliot\TodoRegistrarContracts\Context\ContextNodeInterface;
use Aeliot\TodoRegistrarContracts\Context\PhpContextNodeInterface;
use Aeliot\TodoRegistrarContracts\Context\YamlContextNodeInterface;

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
                PhpContextNodeInterface::KIND_ARROW_FUNCTION => 'Arrow function',
                PhpContextNodeInterface::KIND_CLASS => 'Class: ' . ($node->getName() ?? '{anonymous}'),
                PhpContextNodeInterface::KIND_CLASS_CONST => 'Constant: ' . ($node->getName() ?? '{unknown}'),
                PhpContextNodeInterface::KIND_CLOSURE => 'Closure',
                PhpContextNodeInterface::KIND_ENUM => "Enum: {$node->getName()}",
                PhpContextNodeInterface::KIND_ENUM_CASE => "Enum case: {$node->getName()}",
                PhpContextNodeInterface::KIND_FILE => "File: {$node->getName()}",
                PhpContextNodeInterface::KIND_FUNCTION => "Function: {$node->getName()}()",
                PhpContextNodeInterface::KIND_INTERFACE => "Interface: {$node->getName()}",
                PhpContextNodeInterface::KIND_MATCH => 'Match expression',
                PhpContextNodeInterface::KIND_METHOD => "Method: {$node->getName()}()",
                PhpContextNodeInterface::KIND_NAMESPACE => "Namespace: {$node->getName()}",
                PhpContextNodeInterface::KIND_PARAMETER => 'Parameter: ' . ($node->getName() ?? '{unknown}'),
                PhpContextNodeInterface::KIND_PROPERTY => 'Property: ' . ($node->getName() ?? '{unknown}'),
                PhpContextNodeInterface::KIND_TRAIT => "Trait: {$node->getName()}",
                YamlContextNodeInterface::KIND_DOCUMENT => 'Document: ' . $node->getName(),
                YamlContextNodeInterface::KIND_KEY => 'Key: ' . ($node->getName() ?? '{unknown}'),
                YamlContextNodeInterface::KIND_SEQUENCE_ITEM => 'Sequence item: ' . ($node->getName() ?? '{unknown}'),
                default => ucfirst($node->getKind()) . ($node->getName() ? ": {$node->getName()}" : ''),
            };
        }

        return $lines;
    }
}
