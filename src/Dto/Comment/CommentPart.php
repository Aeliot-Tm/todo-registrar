<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto\Comment;

use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;

final class CommentPart
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

    public function getFirstLine(): string
    {
        if (!$this->lines) {
            throw new \RuntimeException('Cannot get line till injected one');
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
        return $this->tagMetadata?->getPrefixLength();
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
            throw new \RuntimeException('Cannot get line till injected one');
        }

        return implode('', $this->lines);
    }

    public function injectKey(string $key): void
    {
        if (!$this->lines) {
            throw new \RuntimeException('Cannot get line till injected one');
        }

        $prefixLength = (int) $this->tagMetadata?->getPrefixLength();
        if (1 > $prefixLength) {
            throw new \RuntimeException('Cannot get prefix length');
        }

        $line = $this->lines[0];
        $injection = [' ', $key];
        if (' ' !== $line[$prefixLength]) {
            $injection[] = ' ';
        }

        $this->lines[0] = substr($line, 0, $prefixLength) . implode('', $injection) . substr($line, $prefixLength);
    }
}