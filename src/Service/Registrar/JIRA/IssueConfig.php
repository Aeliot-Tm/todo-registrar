<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

class IssueConfig
{
    private bool $addTagToLabels;
    /**
     * @var string[]
     */
    private array $components;
    private string $issueType;
    /**
     * @var string[]
     */
    private array $labels;
    private string $projectKey;
    private string $tagPrefix;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        $issue = $config['issue'];
        $this->addTagToLabels = $issue['addTagToLabels'] ?? false;
        $this->components = (array) ($issue['components'] ?? []);
        $this->issueType = $issue['type'];
        $this->labels = (array) ($issue['labels'] ?? []);
        $this->projectKey = $config['projectKey'];
        $this->tagPrefix = $issue['tagPrefix'] ?? '';
    }

    public function isAddTagToLabels(): bool
    {
        return $this->addTagToLabels;
    }

    /**
     * @return string[]
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    public function getIssueType(): string
    {
        return $this->issueType;
    }

    /**
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getProjectKey(): string
    {
        return $this->projectKey;
    }

    public function getTagPrefix(): string
    {
        return $this->tagPrefix;
    }
}