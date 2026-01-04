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

namespace Aeliot\TodoRegistrar\Service\Registrar\GitLab;

use Aeliot\TodoRegistrar\Service\Registrar\AbstractGeneralIssueConfig;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
final class GeneralIssueConfig extends AbstractGeneralIssueConfig
{
    /**
     * @var string[]|null
     */
    #[Assert\Sequentially([
        new Assert\NotNull(message: 'Option "assignee" is required'),
        new Assert\Type(type: 'array', message: 'Option "assignee" must be an array'),
        new Assert\All([new Assert\Type(type: 'string', message: 'Each assignee must be a string (username or email)')]),
    ])]
    protected mixed $assignee = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'int', message: 'Milestone must be an integer ID'),
            new Assert\Type(type: 'string', message: 'Milestone must be a string title'),
        ],
        message: 'Option "milestone" must be an integer ID, IID, or string title'
    )]
    protected mixed $milestone = null;

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

    #[Assert\NotBlank(message: 'Option "project" is required for GitLab registrar')]
    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\Type(type: 'int', message: 'Option "project" must be an integer'),
            new Assert\Type(type: 'string', message: 'Option "project" must be a string'),
        ],
        message: 'Option "project" must be an integer ID or string name'
    )]
    protected mixed $project = null;

    /**
     * @return string[]
     */
    public function getAssignee(): array
    {
        return $this->assignee;
    }

    public function getMilestone(): int|string|null
    {
        return $this->milestone;
    }

    public function getDueDate(): ?string
    {
        return $this->due_date;
    }

    public function getProject(): int|string
    {
        return $this->project;
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
            'assignee' => [],
            'milestone' => null,
            'due_date' => null,
        ];

        $config['assignee'] = array_filter((array) $config['assignee']);
        $config['due_date'] = isset($config['due_date']) && '' !== $config['due_date'] ? (string) $config['due_date'] : null;

        return $config;
    }
}
