<?php

declare(strict_types=1);

/*
 * This file is part of the TODO Registrar project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Service\Registrar\AbstractGeneralIssueConfig;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
class GeneralIssueConfig extends AbstractGeneralIssueConfig
{
    /**
     * @var string[]|null
     */
    #[Assert\Sequentially([
        new Assert\NotNull(message: 'Option "components" is required'),
        new Assert\Type(type: 'array', message: 'Option "components" must be an array'),
        new Assert\All([new Assert\Type(type: 'string', message: 'Each component must be a string JIRA component name')]),
    ])]
    protected mixed $components = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'string', message: 'Assignee must be a string JIRA username'),
        ],
        message: 'Option "assignee" must be a string JIRA username or null'
    )]
    protected mixed $assignee = null;

    #[Assert\NotBlank(message: 'Option "issueType" is required for JIRA registrar')]
    #[Assert\Type(type: 'string', message: 'Option "issueType" must be a string (e.g., "Task", "Bug", "Story")')]
    protected mixed $issueType = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'string', message: 'Priority must be a string'),
        ],
        message: 'Option "priority" must be a string JIRA priority name (e.g., "High", "Medium", "Low") or null'
    )]
    protected mixed $priority = null;

    #[Assert\NotBlank(message: 'Option "projectKey" is required for JIRA registrar')]
    #[Assert\Type(type: 'string', message: 'Option "projectKey" must be a string (e.g., "PROJ")')]
    protected mixed $projectKey = null;

    #[Assert\IsNull(message: 'Used outdated property "type", but "issueType" must be used')]
    protected mixed $type = null;

    #[Assert\IsNull(message: 'Conflicting config: both "issueType" and "type" are specified. Use only one of them')]
    protected mixed $conflictingTypeKeys = null;

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

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function getProjectKey(): string
    {
        return $this->projectKey;
    }

    /**
     * @param array<string,mixed> $config
     *
     * @return array<string,mixed>
     */
    protected function normalizeConfig(array $config): array
    {
        $config = parent::normalizeConfig($config);
        $config += [
            'assignee' => null,
            'components' => [],
            'priority' => null,
        ];

        $config['components'] = (array) $config['components'];

        if (\array_key_exists('issueType', $config) && \array_key_exists('type', $config)) {
            $config['conflictingTypeKeys'] = '"issueType" and "type"';
        }

        if (\array_key_exists('type', $config) && !\array_key_exists('issueType', $config)) {
            $config['issueType'] = $config['type'];
        }

        return $config;
    }
}
