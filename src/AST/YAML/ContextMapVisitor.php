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

use Aeliot\TodoRegistrar\Dto\Parsing\ContextNode;
use Aeliot\TodoRegistrarContracts\Context\YamlContextNodeInterface;
use Aeliot\YamlToken\Emitter\YamlEmitter;
use Aeliot\YamlToken\Enum\NodeVisitorSignal;
use Aeliot\YamlToken\Node\BlockMappingNode;
use Aeliot\YamlToken\Node\BlockSequenceEntryNode;
use Aeliot\YamlToken\Node\BlockSequenceNode;
use Aeliot\YamlToken\Node\CommentNode;
use Aeliot\YamlToken\Node\DocumentNode;
use Aeliot\YamlToken\Node\DoubleQuotedScalarNode;
use Aeliot\YamlToken\Node\FlowMappingNode;
use Aeliot\YamlToken\Node\FlowSequenceNode;
use Aeliot\YamlToken\Node\KeyValueCoupleNode;
use Aeliot\YamlToken\Node\MultilinePlainScalarNode;
use Aeliot\YamlToken\Node\Node;
use Aeliot\YamlToken\Node\PlainScalarNode;
use Aeliot\YamlToken\Node\ScalarNode;
use Aeliot\YamlToken\Node\SingleQuotedScalarNode;
use Aeliot\YamlToken\Node\StreamNode;
use Aeliot\YamlToken\Node\TokenHolderInterface;
use Aeliot\YamlToken\Node\ValueNode;
use Aeliot\YamlToken\Traversal\NodeVisitorAbstract;

/**
 * Builds context map during AST traversal.
 * Only nested (parent) nodes are tracked, siblings are excluded.
 *
 * @internal
 */
final class ContextMapVisitor extends NodeVisitorAbstract
{
    private const MAX_COMPLEX_KEY_LENGTH = 48;

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
     *
     * @var list<array{startLine: int, endLine: int}>
     */
    private array $commentRanges = [];

    /**
     * @var array<int, list<ContextNode>>
     */
    private array $contextMap = [];

    private int $documentIndex = 0;

    /**
     * @var list<ContextNode>
     */
    private array $stack;

    public function __construct(string $filePath)
    {
        $this->stack = [new ContextNode(YamlContextNodeInterface::KIND_FILE, $filePath)];
    }

    /**
     * @return array<int, list<ContextNode>>
     */
    public function getContextMap(): array
    {
        return $this->contextMap;
    }

    public function beforeTraverse(Node $root): ?Node
    {
        $this->collectAllCommentsPositions($root);

        return null;
    }

    public function enterNode(Node $node): ?NodeVisitorSignal
    {
        $contextNode = $this->createContextNode($node);

        if ($contextNode) {
            $this->stack[] = $contextNode;

            if ($range = $this->getLineRange($node)) {
                for ($line = $range[0]; $line <= $range[1]; ++$line) {
                    $this->contextMap[$line] = [...$this->stack];
                }
            }
        }

        if ($node instanceof KeyValueCoupleNode || $node instanceof BlockSequenceEntryNode) {
            $this->applyLookAheadForNode($node);
        }

        return null;
    }

    public function leaveNode(Node $node): ?NodeVisitorSignal
    {
        if ($this->shouldTrack($node)) {
            array_pop($this->stack);
        }

        return null;
    }

    private function appendSiblingIndexIfNeeded(KeyValueCoupleNode $couple, ?string $name): ?string
    {
        if (null === $name) {
            return null;
        }

        $sameNameSiblings = [];

        foreach ($this->getSiblingKeyValueCouples($couple) as $sibling) {
            if ($name === $this->getCoupleKeyLabel($sibling)) {
                $sameNameSiblings[] = $sibling;
            }
        }

        if (\count($sameNameSiblings) <= 1) {
            return $name;
        }

        foreach ($sameNameSiblings as $index => $sibling) {
            if ($sibling === $couple) {
                return $name . ' #' . $index;
            }
        }

        return $name;
    }

    private function applyLookAheadForNode(KeyValueCoupleNode|BlockSequenceEntryNode $node): void
    {
        $lineRange = $this->getLineRange($node);
        if (null === $lineRange) {
            return;
        }

        $nodeStartLine = $lineRange[0];
        $closestCommentStart = null;
        $closestCommentEnd = null;

        foreach ($this->commentRanges as $commentRange) {
            $commentStart = $commentRange['startLine'];
            $commentEnd = $commentRange['endLine'];

            if ($commentEnd >= $nodeStartLine || !isset($this->contextMap[$commentStart])) {
                continue;
            }

            if (
                (null === $closestCommentStart || $commentEnd > $closestCommentEnd)
                && $this->hasOnlyBlankLinesBetween($commentEnd, $nodeStartLine)
                && $this->isSameParentContext($this->contextMap[$nodeStartLine] ?? [], $this->contextMap[$commentStart])
            ) {
                $closestCommentStart = $commentStart;
                $closestCommentEnd = $commentEnd;
            }
        }

        if (null !== $closestCommentStart && ($contextNode = $this->createContextNode($node))) {
            $this->contextMap[$closestCommentStart][] = $contextNode;
        }
    }

    private function collectAllCommentsPositions(Node $node): void
    {
        if ($node instanceof CommentNode) {
            $startLine = $node->getToken()->line;
            $endLine = $startLine;
            $key = "{$startLine}:{$endLine}";

            if (!isset($this->collectedComments[$key])) {
                $this->commentRanges[] = [
                    'startLine' => $startLine,
                    'endLine' => $endLine,
                ];
                $this->collectedComments[$key] = true;
            }
        }

        foreach ($node->getChildren() as $child) {
            $this->collectAllCommentsPositions($child);
        }
    }

    /**
     * @param list<int> $lines
     */
    private function collectTokenLines(Node $node, array &$lines): void
    {
        if ($node instanceof TokenHolderInterface) {
            $lines[] = $node->getToken()->line;
        }

        foreach ($node->getChildren() as $child) {
            $this->collectTokenLines($child, $lines);
        }
    }

    private function concatMultilinePlainScalar(MultilinePlainScalarNode $node): string
    {
        $parts = [];

        foreach ($node->getChildren() as $child) {
            if ($child instanceof ScalarNode) {
                $parts[] = $child->getToken()->text;
            }
        }

        return implode(' ', $parts);
    }

    private function createContextNode(Node $node): ?ContextNode
    {
        return match (true) {
            $node instanceof DocumentNode => new ContextNode(
                YamlContextNodeInterface::KIND_DOCUMENT,
                (string) $this->documentIndex++
            ),
            $node instanceof KeyValueCoupleNode => new ContextNode(
                YamlContextNodeInterface::KIND_KEY,
                $this->getKeyName($node)
            ),
            $node instanceof BlockSequenceEntryNode => new ContextNode(
                YamlContextNodeInterface::KIND_SEQUENCE_ITEM,
                null !== ($index = $this->getBlockSequenceIndex($node)) ? (string) $index : null,
            ),
            $node instanceof ValueNode && $node->getParent() instanceof FlowSequenceNode => new ContextNode(
                YamlContextNodeInterface::KIND_SEQUENCE_ITEM,
                null !== ($index = $this->getFlowSequenceIndex($node)) ? (string) $index : null,
            ),
            default => null,
        };
    }

    private function extractScalarText(?Node $node): ?string
    {
        return match (true) {
            $node instanceof PlainScalarNode,
            $node instanceof SingleQuotedScalarNode,
            $node instanceof DoubleQuotedScalarNode => $node->getToken()->text,
            $node instanceof MultilinePlainScalarNode => $this->concatMultilinePlainScalar($node),
            default => null,
        };
    }

    private function formatBlockMappingLabel(BlockMappingNode $node): string
    {
        $parts = [];

        foreach ($node->getEntries() as $entry) {
            $parts[] = $this->formatMappingEntryLabel($entry);
        }

        return $this->truncateComplexKey('{' . implode(', ', $parts) . '}');
    }

    private function formatBlockSequenceLabel(BlockSequenceNode $node): string
    {
        $parts = [];

        foreach ($node->getEntries() as $entry) {
            $value = $entry->getValue();
            $parts[] = null !== $value ? ($this->formatValueNodeLabel($value) ?? '…') : '…';
        }

        return $this->truncateComplexKey('[' . implode(', ', $parts) . ']');
    }

    private function formatComplexKeyName(Node $nameNode): string
    {
        return match (true) {
            $nameNode instanceof FlowSequenceNode => $this->formatFlowSequenceLabel($nameNode),
            $nameNode instanceof FlowMappingNode => $this->formatFlowMappingLabel($nameNode),
            $nameNode instanceof BlockSequenceNode => $this->formatBlockSequenceLabel($nameNode),
            $nameNode instanceof BlockMappingNode => $this->formatBlockMappingLabel($nameNode),
            default => (static function () use ($nameNode) {
                if ($nameNode instanceof StreamNode) {
                    $stream = $nameNode;
                } else {
                    $stream = new StreamNode();
                    $stream->addChild($nameNode);
                }

                return preg_replace('/\\s+/', ' ', (new YamlEmitter())->emit($stream));
            })(),
        };
    }

    private function formatFlowMappingLabel(FlowMappingNode $node): string
    {
        $parts = [];

        foreach ($node->getEntries() as $entry) {
            $parts[] = $this->formatMappingEntryLabel($entry);
        }

        return $this->truncateComplexKey('{' . implode(', ', $parts) . '}');
    }

    private function formatFlowSequenceLabel(FlowSequenceNode $node): string
    {
        $parts = [];

        foreach ($node->getEntries() as $entry) {
            $parts[] = $this->formatValueNodeLabel($entry) ?? '…';
        }

        return $this->truncateComplexKey('[' . implode(', ', $parts) . ']');
    }

    private function formatMappingEntryLabel(KeyValueCoupleNode $couple): string
    {
        $keyLabel = $this->getCoupleKeyLabel($couple);
        if (null === $keyLabel) {
            return '…';
        }

        $value = $couple->getValue();
        if (null === $value || $value->isEmpty()) {
            return $keyLabel;
        }

        $valueLabel = $this->formatValueNodeLabel($value);
        if (null === $valueLabel || '' === $valueLabel) {
            return $keyLabel;
        }

        return $keyLabel . ': ' . $valueLabel;
    }

    private function formatValueNodeLabel(ValueNode $valueNode): ?string
    {
        $payload = $valueNode->getPayload();
        if (null === $payload) {
            return null;
        }

        if ($payload instanceof KeyValueCoupleNode) {
            return '{' . $this->formatMappingEntryLabel($payload) . '}';
        }

        if ($this->isComplexKeyNode($payload)) {
            return $this->formatComplexKeyName($payload);
        }

        return $this->extractScalarText($payload);
    }

    private function getBlockSequenceIndex(BlockSequenceEntryNode $entry): ?int
    {
        $parent = $entry->getParent();
        if (!$parent instanceof BlockSequenceNode) {
            return null;
        }

        foreach ($parent->getEntries() as $index => $candidate) {
            if ($candidate === $entry) {
                return $index;
            }
        }

        return null;
    }

    private function getCoupleKeyLabel(KeyValueCoupleNode $couple): ?string
    {
        $nameNode = $couple->getKey()?->getName();
        if (null === $nameNode) {
            return null;
        }

        if ($this->isComplexKeyNode($nameNode)) {
            return $this->formatComplexKeyName($nameNode);
        }

        return $this->extractScalarText($nameNode);
    }

    private function getFlowSequenceIndex(ValueNode $entry): ?int
    {
        $parent = $entry->getParent();
        if (!$parent instanceof FlowSequenceNode) {
            return null;
        }

        foreach ($parent->getEntries() as $index => $candidate) {
            if ($candidate === $entry) {
                return $index;
            }
        }

        return null;
    }

    private function getKeyName(KeyValueCoupleNode $couple): ?string
    {
        return $this->appendSiblingIndexIfNeeded($couple, $this->getCoupleKeyLabel($couple));
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    private function getLineRange(Node $node): ?array
    {
        $lines = [];
        $this->collectTokenLines($node, $lines);

        return [] === $lines ? null : [min($lines), max($lines)];
    }

    /**
     * @return list<KeyValueCoupleNode>
     */
    private function getSiblingKeyValueCouples(KeyValueCoupleNode $couple): array
    {
        $parent = $couple->getParent();
        if (null === $parent) {
            return [$couple];
        }

        if ($parent instanceof BlockMappingNode || $parent instanceof FlowMappingNode) {
            return $parent->getEntries();
        }

        return array_values(
            array_filter(
                $parent->getChildren(),
                static fn (Node $child): bool => $child instanceof KeyValueCoupleNode,
            )
        );
    }

    private function hasOnlyBlankLinesBetween(int $commentEndLine, int $nodeStartLine): bool
    {
        if ($commentEndLine >= $nodeStartLine) {
            return false;
        }

        for ($line = $commentEndLine + 1; $line < $nodeStartLine; ++$line) {
            if (isset($this->contextMap[$line])) {
                return false;
            }
        }

        return true;
    }

    private function isComplexKeyNode(Node $node): bool
    {
        return $node instanceof BlockMappingNode
            || $node instanceof BlockSequenceNode
            || $node instanceof FlowMappingNode
            || $node instanceof FlowSequenceNode;
    }

    /**
     * @param list<ContextNode> $nodeContext
     * @param list<ContextNode> $commentContext
     */
    private function isSameParentContext(array $nodeContext, array $commentContext): bool
    {
        return \count($commentContext) === (\count($nodeContext) - 1);
    }

    private function shouldTrack(Node $node): bool
    {
        return $node instanceof DocumentNode
            || $node instanceof KeyValueCoupleNode
            || $node instanceof BlockSequenceEntryNode
            || ($node instanceof ValueNode && $node->getParent() instanceof FlowSequenceNode);
    }

    private function truncateComplexKey(string $label): string
    {
        if (\strlen($label) <= self::MAX_COMPLEX_KEY_LENGTH) {
            return $label;
        }

        return substr($label, 0, self::MAX_COMPLEX_KEY_LENGTH - 1) . '…';
    }
}
