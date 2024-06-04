<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto\Tag;

final class TagMetadata
{
    public function __construct(
        private ?string $tag = null,
        private ?int $prefixLength = null,
        private ?string $assignee = null,
    ) {
    }

    public function getAssignee(): ?string
    {
        return $this->assignee;
    }

    public function getPrefixLength(): ?int
    {
        return $this->prefixLength;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }
}