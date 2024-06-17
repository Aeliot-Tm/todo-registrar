<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

class IssueConfig
{
    private bool $addTagToLabels;
    private ?string $assignee;
    /**
     * @var string[]
     */
    private array $components;
    private string $issueType;
    /**
     * @var string[]
     */
    private array $labels;
    private ?string $priority;
    private string $projectKey;
    private string $tagPrefix;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        $config = $this->normalizeConfig($config);

        $this->addTagToLabels = $config['addTagToLabels'];
        $this->assignee = $config['assignee'];
        $this->components = $config['components'];
        $this->issueType = $config['type'];
        $this->labels = $config['labels'];
        $this->priority = $config['priority'];
        $this->projectKey = $config['projectKey'];
        $this->tagPrefix = $config['tagPrefix'];
    }

    public function isAddTagToLabels(): bool
    {
        return $this->addTagToLabels;
    }

    public function getAssignee(): ?string
    {
        return $this->assignee;
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

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function getProjectKey(): string
    {
        return $this->projectKey;
    }

    public function getTagPrefix(): string
    {
        return $this->tagPrefix;
    }

    /**
     * @param array<string,mixed> $config
     *
     * @return array<string,mixed>
     */
    public function normalizeConfig(array $config): array
    {
        $config += [
            'addTagToLabels' => false,
            'assignee' => null,
            'components' => [],
            'labels' => [],
            'priority' => null,
            'tagPrefix' => '',
        ];

        $config['addTagToLabels'] = (bool) $config['addTagToLabels'];
        $config['components'] = (array) $config['components'];
        $config['labels'] = (array) $config['labels'];

        return $config;
    }
}