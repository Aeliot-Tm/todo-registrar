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
        $issueDefaults = [
            'addTagToLabels' => false,
            'assignee' => null,
            'components' => [],
            'labels' => [],
            'priority' => null,
            'tagPrefix' => '',
        ];
        $issue = $config['issue'] + $issueDefaults;
        $this->addTagToLabels = (bool) $issue['addTagToLabels'];
        $this->assignee = $issue['assignee'];
        $this->components = (array) $issue['components'];
        $this->issueType = $issue['type'];
        $this->labels = (array) $issue['labels'];
        $this->priority = $issue['priority'];
        $this->projectKey = $config['projectKey'];
        $this->tagPrefix = $issue['tagPrefix'];
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
}