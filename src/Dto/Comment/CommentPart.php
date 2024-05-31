<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto\Comment;

final class CommentPart
{
    /**
     * @var string[]
     */
    protected array $lines;

    public function __construct(
        private ?string $tag,
        private ?int $position,
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }
}