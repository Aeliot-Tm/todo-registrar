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

namespace Aeliot\TodoRegistrar\AST\YAML;

use Aeliot\TodoRegistrar\Dto\Parsing\ContextMapBuilderInterface;
use Aeliot\YamlToken\Node\StreamNode;
use Aeliot\YamlToken\Traversal\NodeTraverser;

/**
 * @internal
 */
final readonly class ContextMapBuilder implements ContextMapBuilderInterface
{
    public function __construct(
        private StreamNode $streamNode,
        private string $filePath,
    ) {
    }

    public function buildContextMap(): array
    {
        $visitor = new ContextMapVisitor($this->filePath);

        $traverser = new NodeTraverser($visitor);
        $traverser->traverse($this->streamNode);

        return $visitor->getContextMap();
    }
}
