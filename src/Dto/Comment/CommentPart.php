<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto\Comment;

use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Exception\NoLineException;
use Aeliot\TodoRegistrar\Exception\NoPrefixException;

class CommentPart
{
    /**
     * @var string[]
     */
    private array $lines = [];

    public function __construct(
        private ?TagMetadata $tagMetadata,
    ) {
    }

    public function addLine(string $line): void
    {
        $this->lines[] = $line;
    }

    public function getDescription(): string
    {
        if (!$this->lines) {
            throw new NoLineException('Cannot get description till added one line');
        }

        $prefixLength = (int) $this->tagMetadata?->getPrefixLength();
        $lines = array_map(static fn(string $line): string => substr($line, $prefixLength), $this->lines);

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

    public function getContent(): string
    {
        if (!$this->lines) {
            throw new NoLineException('Cannot get content till added one line');
        }

        return implode('', $this->lines);
    }

    public function injectKey(string $key): void
    {
        if (!$this->lines) {
            throw new NoLineException('Cannot inject key till added one line');
        }

        $prefixLength = (int) $this->tagMetadata?->getPrefixLength();
        if (1 > $prefixLength) {
            throw new NoPrefixException('Cannot get prefix length');
        }

        $line = $this->lines[0];
        $injection = [' ', $key];
        if (' ' !== $line[$prefixLength]) {
            $injection[] = ' ';
        }

        $this->lines[0] = substr($line, 0, $prefixLength) . implode('', $injection) . substr($line, $prefixLength);
    }
}