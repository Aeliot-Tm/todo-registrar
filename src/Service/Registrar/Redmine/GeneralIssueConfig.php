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

namespace Aeliot\TodoRegistrar\Service\Registrar\Redmine;

use Aeliot\TodoRegistrar\Service\Registrar\AbstractGeneralIssueConfig;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
final class GeneralIssueConfig extends AbstractGeneralIssueConfig
{
    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'string', message: 'Assignee must be a string (username, login, or email)'),
            new Assert\Type(type: 'int', message: 'Assignee must be an integer user ID'),
        ],
        message: 'Option "assignee" must be a string (username/login/email), integer user ID, or null'
    )]
    protected mixed $assignee = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'int', message: 'Category must be an integer ID'),
            new Assert\Type(type: 'string', message: 'Category must be a string name'),
        ],
        message: 'Option "category" must be an integer ID, string name, or null'
    )]
    protected mixed $category = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Regex(
                pattern: '/^\d{4}-\d{2}-\d{2}$/',
                message: 'Due date must be in format YYYY-MM-DD'
            ),
        ],
        message: 'Option "due_date" must be a date string in format YYYY-MM-DD or null'
    )]
    protected mixed $due_date = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'float', message: 'Estimated hours must be a float'),
        ],
        message: 'Option "estimated_hours" must be a float or null'
    )]
    protected mixed $estimated_hours = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'int', message: 'Fixed version must be an integer ID'),
            new Assert\Type(type: 'string', message: 'Fixed version must be a string name'),
        ],
        message: 'Option "fixed_version" must be an integer ID, string name, or null'
    )]
    protected mixed $fixed_version = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'int', message: 'Priority must be an integer ID'),
            new Assert\Type(type: 'string', message: 'Priority must be a string name'),
        ],
        message: 'Option "priority" must be an integer ID, string name, or null'
    )]
    protected mixed $priority = null;

    #[Assert\NotBlank(message: 'Option "project" is required for Redmine registrar')]
    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\Type(type: 'int', message: 'Option "project" must be an integer'),
            new Assert\Type(type: 'string', message: 'Option "project" must be a string'),
        ],
        message: 'Option "project" must be an integer ID or string name'
    )]
    protected mixed $project = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Regex(
                pattern: '/^\d{4}-\d{2}-\d{2}$/',
                message: 'Start date must be in format YYYY-MM-DD'
            ),
        ],
        message: 'Option "start_date" must be a date string in format YYYY-MM-DD or null'
    )]
    protected mixed $start_date = null;

    #[Assert\NotBlank(message: 'Option "tracker" is required for Redmine registrar')]
    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\Type(type: 'int', message: 'Tracker must be an integer ID'),
            new Assert\Type(type: 'string', message: 'Tracker must be a string name'),
        ],
        message: 'Option "tracker" must be an integer ID or string name'
    )]
    protected mixed $tracker = null;

    public function getAssignee(): int|string|null
    {
        return $this->assignee;
    }

    public function getCategory(): int|string|null
    {
        return $this->category;
    }

    public function getDueDate(): ?string
    {
        return $this->due_date;
    }

    public function getEstimatedHours(): ?float
    {
        return $this->estimated_hours;
    }

    public function getFixedVersion(): int|string|null
    {
        return $this->fixed_version;
    }

    public function getPriority(): int|string|null
    {
        return $this->priority;
    }

    public function getProjectIdentifier(): int|string
    {
        return $this->project;
    }

    public function getStartDate(): ?string
    {
        return $this->start_date;
    }

    public function getTracker(): int|string|null
    {
        return $this->tracker;
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
            'due_date' => null,
            'estimated_hours' => null,
            'start_date' => null,
        ];

        $config['start_date'] = trim((string) ($config['start_date'] ?? '')) ?: null;
        $config['due_date'] = trim((string) ($config['due_date'] ?? '')) ?: null;
        $config['estimated_hours'] = isset($config['estimated_hours']) ? (float) $config['estimated_hours'] : null;

        return $config;
    }
}
