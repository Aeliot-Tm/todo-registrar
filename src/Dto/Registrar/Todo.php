<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto\Registrar;

class Todo
{
    /**
     * @param array<array-key,mixed> $inlineConfig
     */
    public function __construct(
        private string $tag,
        private string $summary,
        private string $description,
        private ?string $assignee,
        private array $inlineConfig,
    ) {
    }

    public function getAssignee(): ?string
    {
        return $this->assignee;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getInlineConfig(): array
    {
        return $this->inlineConfig;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}
