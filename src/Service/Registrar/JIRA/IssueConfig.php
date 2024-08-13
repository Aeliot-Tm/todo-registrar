<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Exception\InvalidConfigException;

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
    private ?string $summaryPrefix;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        if (\array_key_exists('issueType', $config) && \array_key_exists('type', $config)) {
            $exceptionMessage = 'Conflicting config. Both properties "issueType" and "type" added to config of issue';
            throw new InvalidConfigException($exceptionMessage);
        }

        $config = $this->normalizeConfig($config);
        foreach ((new \ReflectionClass($this))->getProperties() as $property) {
            $key = $property->getName();
            if (!\array_key_exists($key, $config)) {
                throw new InvalidConfigException("Undefined property of issue config: {$key}");
            }

            $this->$key = $config[$key];
        }
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

    public function getSummaryPrefix(): string
    {
        return $this->summaryPrefix;
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
            'summaryPrefix' => '',
            'tagPrefix' => '',
        ];

        $config['addTagToLabels'] = (bool) $config['addTagToLabels'];
        $config['components'] = (array) $config['components'];
        $config['labels'] = (array) $config['labels'];

        if (\array_key_exists('type', $config)) {
            // TODO: throw exception when exists key "issueType"
            $config['issueType'] = $config['type'];
            unset($config['type']);
        }

        return $config;
    }
}
