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
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
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
     * Set of already collected comment identifiers to avoid duplicates.
     * Format: "startLine:endLine" => true.
     *
     * @var array<string, true>
     */
    private array $collectedComments = [];

    /**
     * List of all comment ranges in the file.
     * Collected once before traversal.
     * Supports multiple comments on the same line.
     *
     * @var list<array{startLine: int, endLine: int}>
     */
    private array $commentRanges = [];

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
        $this->stack = [new ContextNode(ContextNodeInterface::KIND_FILE, $filePath)];
    }

    /**
     * @return array<int, list<ContextNode>>
     */
    public function getContextMap(): array
    {
        return $this->contextMap;
    }

    /**
     * Collect all comments before traversal (called once).
     *
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->collectAllComments($nodes);

        return null;
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

        if (
            $node instanceof ClassConst
            || $node instanceof EnumCase
            || $node instanceof Param
            || $node instanceof Property
        ) {
            $this->applyLookAheadForNode($node);
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

    /**
     * Recursively collect all comments from AST.
     *
     * @param Node[] $nodes
     */
    private function collectAllComments(array $nodes): void
    {
        foreach ($nodes as $node) {
            foreach ($node->getComments() as $comment) {
                $startLine = $comment->getStartLine();
                $endLine = $comment->getEndLine();
                $key = "{$startLine}:{$endLine}";

                // Skip if already collected (same comment attached to multiple nodes)
                if (isset($this->collectedComments[$key])) {
                    continue;
                }

                $this->commentRanges[] = [
                    'startLine' => $startLine,
                    'endLine' => $endLine,
                ];

                $this->collectedComments[$key] = true;
            }

            // Recursively collect from child nodes
            foreach ($node->getSubNodeNames() as $name) {
                $subNode = $node->$name;

                if (\is_array($subNode)) {
                    $this->collectAllComments($subNode);
                } elseif ($subNode instanceof Node) {
                    $this->collectAllComments([$subNode]);
                }
            }
        }
    }

    private function createContextNode(Node $node): ?ContextNode
    {
        return match (true) {
            $node instanceof ArrowFunction => new ContextNode(
                ContextNodeInterface::KIND_ARROW_FUNCTION,
                null
            ),
            $node instanceof Class_ => new ContextNode(
                ContextNodeInterface::KIND_CLASS,
                $node->name?->toString()
            ),
            $node instanceof ClassConst => new ContextNode(
                ContextNodeInterface::KIND_CLASS_CONST,
                $this->getClassConstName($node)
            ),
            $node instanceof Closure => new ContextNode(
                ContextNodeInterface::KIND_CLOSURE,
                null
            ),
            $node instanceof Enum_ => new ContextNode(
                ContextNodeInterface::KIND_ENUM,
                $node->name->toString()
            ),
            $node instanceof EnumCase => new ContextNode(
                ContextNodeInterface::KIND_ENUM_CASE,
                $node->name->toString()
            ),
            $node instanceof Function_ => new ContextNode(
                ContextNodeInterface::KIND_FUNCTION,
                $node->name->toString()
            ),
            $node instanceof Interface_ => new ContextNode(
                ContextNodeInterface::KIND_INTERFACE,
                $node->name->toString()
            ),
            $node instanceof Match_ => new ContextNode(
                ContextNodeInterface::KIND_MATCH,
                null
            ),
            $node instanceof ClassMethod => new ContextNode(
                ContextNodeInterface::KIND_METHOD,
                $node->name->toString()
            ),
            $node instanceof Namespace_ => new ContextNode(
                ContextNodeInterface::KIND_NAMESPACE,
                $node->name?->toString()
            ),
            $node instanceof Param => new ContextNode(
                ContextNodeInterface::KIND_PARAMETER,
                $node->var instanceof Node\Expr\Variable && \is_string($node->var->name) ? $node->var->name : null
            ),
            $node instanceof Property => new ContextNode(
                ContextNodeInterface::KIND_PROPERTY,
                $this->getPropertyName($node)
            ),
            $node instanceof Trait_ => new ContextNode(
                ContextNodeInterface::KIND_TRAIT,
                $node->name->toString()
            ),
            default => null,
        };
    }

    /**
     * Apply look-ahead for a specific Property/Param/ClassConst/EnumCase node.
     * Finds the closest comment before the node (with only attributes/blank lines between).
     */
    private function applyLookAheadForNode(Property|Param|ClassConst|EnumCase $node): void
    {
        $nodeStartLine = $node->getStartLine();
        $closestCommentStart = null;
        $closestCommentEnd = null;

        foreach ($this->commentRanges as $commentRange) {
            $commentStart = $commentRange['startLine'];
            $commentEnd = $commentRange['endLine'];

            if ($commentEnd >= $nodeStartLine) {
                continue;
            }

            if (!isset($this->contextMap[$commentStart])) {
                continue;
            }

            $commentContext = $this->contextMap[$commentStart];
            $nodeContext = $this->contextMap[$nodeStartLine] ?? [];

            if (
                (null === $closestCommentStart || $commentEnd > $closestCommentEnd)
                && $this->hasOnlyAttributesAndBlankLinesBetween($commentEnd, $nodeStartLine)
                && $this->isSameParentContext($commentContext, $nodeContext, $node)
            ) {
                $closestCommentStart = $commentStart;
                $closestCommentEnd = $commentEnd;
            }
        }

        if (null !== $closestCommentStart) {
            $contextNode = $this->createContextNode($node);
            if ($contextNode) {
                $this->contextMap[$closestCommentStart][] = $contextNode;
            }
        }
    }

    /**
     * Get class constant name(s).
     * Multiple constants can be declared in one statement: const A = 1, B = 2;.
     */
    private function getClassConstName(ClassConst $node): ?string
    {
        if (empty($node->consts)) {
            return null;
        }

        $names = array_map(static fn (Const_ $const) => $const->name->toString(), $node->consts);

        return implode(', ', $names);
    }

    /**
     * Get property name(s).
     * Multiple properties can be declared in one statement: private $a, $b;.
     */
    private function getPropertyName(Property $node): ?string
    {
        if (empty($node->props)) {
            return null;
        }

        $names = array_map(static fn (PropertyItem $prop) => $prop->name->toString(), $node->props);

        return implode(', ', $names);
    }

    /**
     * Check if there are only attributes and blank lines between comment end and node start.
     * Uses contextMap to detect non-attribute code.
     */
    private function hasOnlyAttributesAndBlankLinesBetween(int $commentEndLine, int $nodeStartLine): bool
    {
        if ($commentEndLine >= $nodeStartLine) {
            return false;
        }

        // If there's any context change between comment and node (except attributes),
        // it means there's some code between them
        for ($line = $commentEndLine + 1; $line < $nodeStartLine; ++$line) {
            // Skip lines that are not in contextMap (blank lines or comments)
            if (!isset($this->contextMap[$line])) {
                continue;
            }

            // If line has context, but it's not from an attribute, there's code between
            // We check if this line belongs to the same context as the comment
            // Attributes don't create their own context levels
            $lineContext = $this->contextMap[$line];
            $commentContext = $this->contextMap[$commentEndLine] ?? $this->contextMap[$commentEndLine - 1] ?? [];

            // If contexts differ, there's a new code structure between comment and node
            if ($lineContext !== $commentContext) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if comment and node have the same parent context.
     * For parameters, parent is method/function. For properties, parent is class.
     *
     * @param list<ContextNode> $commentContext
     * @param list<ContextNode> $nodeContext
     */
    private function isSameParentContext(array $commentContext, array $nodeContext, Node $node): bool
    {
        if ($node instanceof Param) {
            $commentParentCount = \count($commentContext);
            $nodeParentCount = \count($nodeContext) - 1;

            if ($commentParentCount !== $nodeParentCount) {
                return false;
            }

            $lastCommentContext = end($commentContext);
            if (!$lastCommentContext) {
                return false;
            }

            return \in_array($lastCommentContext->getKind(), [
                ContextNodeInterface::KIND_METHOD,
                ContextNodeInterface::KIND_FUNCTION,
                ContextNodeInterface::KIND_CLOSURE,
                ContextNodeInterface::KIND_ARROW_FUNCTION,
            ], true);
        }

        if ($node instanceof Property || $node instanceof ClassConst) {
            $commentParentCount = \count($commentContext);
            $nodeParentCount = \count($nodeContext) - 1;

            if ($commentParentCount !== $nodeParentCount) {
                return false;
            }

            $lastCommentContext = end($commentContext);
            if (!$lastCommentContext) {
                return false;
            }

            return \in_array($lastCommentContext->getKind(), [
                ContextNodeInterface::KIND_CLASS,
                ContextNodeInterface::KIND_TRAIT,
            ], true);
        }

        if ($node instanceof EnumCase) {
            $commentParentCount = \count($commentContext);
            $nodeParentCount = \count($nodeContext) - 1;

            if ($commentParentCount !== $nodeParentCount) {
                return false;
            }

            $lastCommentContext = end($commentContext);
            if (!$lastCommentContext) {
                return false;
            }

            return ContextNodeInterface::KIND_ENUM === $lastCommentContext->getKind();
        }

        return false;
    }

    private function shouldTrack(Node $node): bool
    {
        return $node instanceof ArrowFunction
            || $node instanceof Class_
            || $node instanceof ClassConst
            || $node instanceof ClassMethod
            || $node instanceof Closure
            || $node instanceof Enum_
            || $node instanceof EnumCase
            || $node instanceof Function_
            || $node instanceof Interface_
            || $node instanceof Namespace_
            || $node instanceof Match_
            || $node instanceof Property
            || $node instanceof Param
            || $node instanceof Trait_;
    }
}
