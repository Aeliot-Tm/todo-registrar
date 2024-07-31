<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto\Tag;

class TagMetadata
{
    public function __construct(
        private ?string $tag = null,
        private ?int $prefixLength = null,
        private ?string $assignee = null,
        private ?string $ticketKey = null,
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

    public function getTicketKey(): ?string
    {
        return $this->ticketKey;
    }
}
