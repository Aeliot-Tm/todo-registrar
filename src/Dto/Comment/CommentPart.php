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

namespace Aeliot\TodoRegistrar\Dto\Comment;

use Aeliot\TodoRegistrar\Dto\Parsing\ContextInterface;
use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Aeliot\TodoRegistrar\Enum\IssueKeyPosition;
use Aeliot\TodoRegistrar\Exception\NoLineException;
use Aeliot\TodoRegistrar\Exception\NoPrefixException;

/**
 * @internal
 */
final class CommentPart
{
    /**
     * @var string[]
     */
    private array $lines = [];

    /**
     * @var string[]|null
     */
    private ?array $cleanLines = null;

    public function __construct(
        private int $startLine,
        private ?TagMetadata $tagMetadata,
        private ContextInterface $context,
        private TokenInterface $token,
    ) {
    }

    public function addLine(string $line): void
    {
        $this->lines[] = $line;
    }

    public function getContent(): string
    {
        if (!$this->lines) {
            throw new NoLineException('Cannot get content till added one line');
        }

        return implode('', $this->lines);
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function getDescription(): string
    {
        if (!$this->lines) {
            throw new NoLineException('Cannot get description till not added at least one line');
        }

        $cleanLines = $this->getCleanLines();
        $firstLine = array_shift($cleanLines);

        if (!$cleanLines) {
            return '';
        }

        $baseIndent = $this->calculateBaseIndentFromFirstLine($firstLine);
        $minIndent = $this->calculateMinIndent($cleanLines);

        if (\PHP_INT_MAX === $minIndent) {
            return implode('', $cleanLines);
        }

        $indentToRemove = max(0, $minIndent - $baseIndent);

        $processedLines = array_map(static function (string $line) use ($indentToRemove): string {
            if ('' === trim($line)) {
                return $line;
            }

            return substr($line, $indentToRemove);
        }, $cleanLines);

        return implode('', $processedLines);
    }

    public function getFirstLine(): string
    {
        if (!$this->lines) {
            throw new NoLineException('Cannot get first line till added one');
        }

        return reset($this->lines);
    }

    /**
     * @return string[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function getPrefixLength(): ?int
    {
        // THINK: throw exception when there is no prefix
        return $this->tagMetadata?->getPrefixLength();
    }

    public function getSummary(): string
    {
        return trim(substr($this->getFirstLine(), $this->getPrefixLength()));
    }

    public function getTag(): ?string
    {
        return $this->tagMetadata?->getTag();
    }

    public function getTagMetadata(): ?TagMetadata
    {
        return $this->tagMetadata;
    }

    public function getStartLine(): int
    {
        return $this->startLine;
    }

    public function injectKey(string $key, IssueKeyPosition $position, ?string $newSeparator, bool $replaceSeparator): void
    {
        if (!$this->lines) {
            throw new NoLineException('Cannot inject key till added one line');
        }

        $offset = $this->getSeparatorOffset($position);
        $line = $this->lines[0];
        $separatorOffset = $this->tagMetadata?->getSeparatorOffset();

        if ($replaceSeparator && null !== $newSeparator && null !== $separatorOffset) {
            $line[$separatorOffset] = $newSeparator;
        }

        [$before, $after, $middleSpace] = $this->splitLine($line, $offset);

        $parts = [$before];
        $parts[] = $middleSpace->getSpace();

        if (null !== $newSeparator && null === $separatorOffset && $position->isAfterSeparator()) {
            $parts[] = $newSeparator;
            $parts[] = $middleSpace->getSpace();
        }

        if ($position->isBeforeSeparatorSticky()) {
            $parts[] = $middleSpace->grabTail();
        }

        $parts[] = $key;
        if (!$position->isBeforeSeparatorSticky()) {
            $parts[] = $middleSpace->getSpace();
        }

        if (null !== $newSeparator && null === $separatorOffset && $position->isBeforeSeparator()) {
            $parts[] = $newSeparator;
            $parts[] = $middleSpace->getSpace();
        }

        $parts[] = $middleSpace->grabTail();
        $parts[] = $after;

        $this->lines[0] = implode('', $parts);
    }

    private function getSeparatorOffset(IssueKeyPosition $position): int
    {
        $prefixLength = (int) $this->tagMetadata?->getPrefixLength();
        if (1 > $prefixLength) {
            throw new NoPrefixException('Cannot get prefix length');
        }

        $separatorOffset = $this->tagMetadata?->getSeparatorOffset();
        if (null === $separatorOffset) {
            $offset = $prefixLength;
        } else {
            $offset = match ($position) {
                IssueKeyPosition::AFTER_SEPARATOR => $separatorOffset + 1,
                IssueKeyPosition::BEFORE_SEPARATOR,
                IssueKeyPosition::BEFORE_SEPARATOR_STICKY => $separatorOffset,
            };
        }

        return $offset;
    }

    /**
     * @return string[]
     */
    private function getCleanLines(): array
    {
        if (null === $this->cleanLines) {
            $cleanText = $this->token->getCleanText();
            $lines = explode("\n", $cleanText);
            $this->cleanLines = array_map(static fn (string $line): string => $line . "\n", $lines);
            $lastIndex = \count($this->cleanLines) - 1;
            $this->cleanLines[$lastIndex] = rtrim($this->cleanLines[$lastIndex], "\n");
        }

        return $this->cleanLines;
    }

    private function calculateBaseIndentFromFirstLine(string $firstLine): int
    {
        if (preg_match('/^(\s*)/', $firstLine, $matches)) {
            return \strlen($matches[1]);
        }

        return 0;
    }

    /**
     * @param string[] $lines
     */
    private function calculateMinIndent(array $lines): int
    {
        $minIndent = \PHP_INT_MAX;

        foreach ($lines as $line) {
            if ('' === trim($line)) {
                continue;
            }

            if (preg_match('/^(\s*)/', $line, $matches)) {
                $indent = \strlen($matches[1]);
                $minIndent = min($minIndent, $indent);
            }
        }

        return \PHP_INT_MAX === $minIndent ? 0 : $minIndent;
    }

    /**
     * @return array{0: string, 1: string, 2: MiddleSpace}
     */
    private function splitLine(string $line, int $offset): array
    {
        $before = substr($line, 0, $offset);
        $after = substr($line, $offset);
        $middleSpace = new MiddleSpace();
        if (preg_match('/(\s+)$/', $before, $matches)) {
            $middleSpace->addSpace($spaces = $matches[1]);
            $before = substr($before, 0, -1 * \strlen($spaces));
        }

        if (preg_match('/^(\s+)/', $after, $matches)) {
            $middleSpace->addSpace($spaces = $matches[1]);
            $after = substr($after, \strlen($spaces));
        }

        return [$before, $after, $middleSpace];
    }
}
