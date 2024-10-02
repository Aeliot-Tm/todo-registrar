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

use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Service\Registrar\AbstractIssueConfig;

class IssueConfig extends AbstractIssueConfig
{
    /**
     * @var string[]
     */
    protected array $components;
    protected ?string $assignee;
    protected string $issueType;
    protected ?string $priority;
    protected string $projectKey;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        if (\array_key_exists('issueType', $config) && \array_key_exists('type', $config)) {
            $exceptionMessage = 'Conflicting config. Both properties "issueType" and "type" added to config of issue';
            throw new InvalidConfigException($exceptionMessage);
        }

        parent::__construct($config);
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

        if (\array_key_exists('type', $config)) {
            // TODO: #127 throw exception when exists key "issueType"
            $config['issueType'] = $config['type'];
            unset($config['type']);
        }

        return $config;
    }
}
