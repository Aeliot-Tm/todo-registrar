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

    /**
     * @return array<string,mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function setTitle(string $title): void
    {
        $this->data['title'] = $title;
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

    /**
     * @param string[] $labels
     */
    public function setLabels(array $labels): void
    {
        if (!empty($labels)) {
            // GitLab API requires labels as comma-separated string
            $this->data['labels'] = implode(',', array_unique($labels));
        }
    }

    public function setMilestoneId(?int $milestoneId): void
    {
        if (null !== $milestoneId) {
            $this->data['milestone_id'] = $milestoneId;
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
        if (!isset($this->data['labels']) || '' === $this->data['labels']) {
            return [];
        }

        // Labels are stored as comma-separated string in GitLab
        return array_map('trim', explode(',', $this->data['labels']));
    }
}
