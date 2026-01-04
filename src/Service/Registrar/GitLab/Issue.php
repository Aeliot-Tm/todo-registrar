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

/**
 * @internal
 */
final class Issue
{
    /**
     * @var array<string,mixed>
     */
    private array $data = [];

    private int|string $project;

    /**
     * @return array<string,mixed>
     */
    public function getData(): array
    {
        $data = $this->data;
        if ($labels = $data['labels'] ?? null) {
            $data['labels'] = implode(', ', $labels);
        }

        return $data;
    }

    public function setDescription(string $description): void
    {
        $this->data['description'] = $description;
    }

    /**
     * @param int[] $assigneeIds
     */
    public function setAssigneeIds(array $assigneeIds): void
    {
        if (!empty($assigneeIds)) {
            $this->data['assignee_ids'] = $assigneeIds;
        }
    }

    public function setDueDate(?string $dueDate): void
    {
        if (null !== $dueDate && '' !== $dueDate) {
            $this->data['due_date'] = $dueDate;
        }
    }

    /**
     * Get labels as array.
     *
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->data['labels'];
    }

    /**
     * @param string[] $labels
     */
    public function setLabels(array $labels): void
    {
        $this->data['labels'] = $labels;
    }

    public function setMilestoneId(?int $milestoneId): void
    {
        if (null !== $milestoneId) {
            $this->data['milestone_id'] = $milestoneId;
        }
    }

    public function getProject(): int|string
    {
        return $this->project;
    }

    public function setProject(int|string $project): void
    {
        $this->project = $project;
    }

    public function setTitle(string $title): void
    {
        $this->data['title'] = $title;
    }
}
