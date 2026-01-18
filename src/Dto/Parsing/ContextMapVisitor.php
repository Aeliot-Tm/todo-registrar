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

use Aeliot\TodoRegistrarContracts\ContextNodeInterface;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Builds context map during AST traversal.
 * Only nested (parent) nodes are tracked, siblings are excluded.
 *
 * @internal
 */
final class ContextMapVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<int, list<ContextNode>>
     */
    private array $contextMap = [];

    /**
     * @var list<ContextNode>
     */
    private array $stack;

    public function __construct(string $filePath)
    {
        $this->stack = [ContextNode::file($filePath)];
    }

    /**
     * @return array<int, list<ContextNode>>
     */
    public function getContextMap(): array
    {
        return $this->contextMap;
    }

    public function enterNode(Node $node): ?int
    {
        $contextNode = $this->createContextNode($node);

        if ($contextNode) {
            $this->stack[] = $contextNode;

            for ($line = $node->getStartLine(); $line <= $node->getEndLine(); ++$line) {
                $this->contextMap[$line] = [...$this->stack];
            }
        }

        return null;
    }

    public function leaveNode(Node $node): ?int
    {
        if ($this->shouldTrack($node)) {
            array_pop($this->stack);
        }

        return null;
    }

    private function createContextNode(Node $node): ?ContextNode
    {
        return match (true) {
            $node instanceof Node\Expr\ArrowFunction => new ContextNode(
                ContextNodeInterface::KIND_ARROW_FUNCTION,
                null
            ),
            $node instanceof Node\Stmt\Class_ => new ContextNode(
                ContextNodeInterface::KIND_CLASS,
                $node->name?->toString()
            ),
            $node instanceof Node\Expr\Closure => new ContextNode(
                ContextNodeInterface::KIND_CLOSURE,
                null
            ),
            $node instanceof Node\Stmt\Enum_ => new ContextNode(
                ContextNodeInterface::KIND_ENUM,
                $node->name->toString()
            ),
            $node instanceof Node\Stmt\Function_ => new ContextNode(
                ContextNodeInterface::KIND_FUNCTION,
                $node->name->toString()
            ),
            $node instanceof Node\Stmt\Interface_ => new ContextNode(
                ContextNodeInterface::KIND_INTERFACE,
                $node->name->toString()
            ),
            $node instanceof Node\Expr\Match_ => new ContextNode(
                ContextNodeInterface::KIND_MATCH,
                null
            ),
            $node instanceof Node\Stmt\ClassMethod => new ContextNode(
                ContextNodeInterface::KIND_METHOD,
                $node->name->toString()
            ),
            $node instanceof Node\Stmt\Namespace_ => new ContextNode(
                ContextNodeInterface::KIND_NAMESPACE,
                $node->name?->toString()
            ),
            $node instanceof Node\Stmt\Trait_ => new ContextNode(
                ContextNodeInterface::KIND_TRAIT,
                $node->name->toString()
            ),
            default => null,
        };
    }

    private function shouldTrack(Node $node): bool
    {
        return $node instanceof Node\Expr\ArrowFunction
            || $node instanceof Node\Stmt\Class_
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Expr\Closure
            || $node instanceof Node\Stmt\Enum_
            || $node instanceof Node\Stmt\Function_
            || $node instanceof Node\Stmt\Interface_
            || $node instanceof Node\Stmt\Namespace_
            || $node instanceof Node\Expr\Match_
            || $node instanceof Node\Stmt\Trait_;
    }
}
