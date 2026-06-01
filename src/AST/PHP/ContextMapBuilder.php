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

namespace Aeliot\TodoRegistrar\AST\PHP;

use Aeliot\TodoRegistrar\Dto\Parsing\ContextMapBuilderInterface;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;

/**
 * @internal
 */
final readonly class ContextMapBuilder implements ContextMapBuilderInterface
{
    /**
     * @param array<Stmt> $ast
     */
    public function __construct(
        private array $ast,
        private string $filePath,
    ) {
    }

    public function buildContextMap(): array
    {
        $visitor = new ContextMapVisitor($this->filePath);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($this->ast);

        return $visitor->getContextMap();
    }
}
