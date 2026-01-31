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

    public function __construct(
        private \PhpToken $token,
        private ?TagMetadata $tagMetadata,
        private ContextInterface $context,
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

        $prefixLength = (int) $this->tagMetadata?->getPrefixLength();
        $lines = $this->lines;
        array_shift($lines);
        $lines = array_map(static fn (string $line): string => substr($line, $prefixLength), $lines);

        return implode('', $lines);
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

    public function getToken(): \PhpToken
    {
        return $this->token;
    }

    public function injectKey(string $key, IssueKeyPosition $position): void
    {
        if (!$this->lines) {
            throw new NoLineException('Cannot inject key till added one line');
        }

        $prefixLength = (int) $this->tagMetadata?->getPrefixLength();
        if (1 > $prefixLength) {
            throw new NoPrefixException('Cannot get prefix length');
        }

        $line = $this->lines[0];
        $separatorOffset = $this->tagMetadata?->getSeparatorOffset();
        if (null === $separatorOffset) {
            $offset = $prefixLength;
        } else {
            $offset = match ($position) {
                IssueKeyPosition::BEFORE_SEPARATOR => $separatorOffset,
                IssueKeyPosition::AFTER_SEPARATOR => $separatorOffset + 1,
            };
        }

        $before = substr($line, 0, $offset);
        $after = substr($line, $offset);
        $hasSpaceAfter = false;
        $hasSpaceBefore = false;
        if (preg_match('/(\s+)$/', $before, $matches)) {
            $spaces = $matches[1];
            $hasSpaceBefore = true;
            if (\strlen($spaces) > 1) {
                $after = substr($before, -1) . $after;
                $before = substr($before, 0, -1);
                $hasSpaceAfter = true;
            }
        }

        if (preg_match('/^(\s+)/', $after, $matches)) {
            $spaces = $matches[1];
            $hasSpaceAfter = true;
            if (!$hasSpaceBefore && \strlen($spaces) > 1) {
                $before .= $after[0];
                $after = substr($after, 1);
                $hasSpaceBefore = true;
            }
        }

        $parts = [$before];
        if (!$hasSpaceBefore) {
            $parts[] = ' ';
        }

        $parts[] = $key;
        if (!$hasSpaceAfter) {
            $parts[] = ' ';
        }

        $parts[] = $after;

        $this->lines[0] = implode('', $parts);
    }
}
