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

use Aeliot\TodoRegistrar\Service\Registrar\AbstractIssueConfig;

final class IssueConfig extends AbstractIssueConfig
{
    /**
     * @var string[]
     */
    protected array $assignee;
    protected int|string|null $milestone;
    protected ?string $due_date;

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
