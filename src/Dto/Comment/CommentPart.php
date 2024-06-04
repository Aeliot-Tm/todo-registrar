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

    public function getContent(): string
    {
        if (!$this->lines) {
            throw new \RuntimeException('Cannot get line till injected one');
        }

        return implode('', $this->lines);
    }
}