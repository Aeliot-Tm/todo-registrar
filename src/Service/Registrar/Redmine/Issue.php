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

    public function setSubject(string $subject): void
    {
        $this->data['subject'] = $subject;
    }

    public function setDescription(string $description): void
    {
        $this->data['description'] = $description;
    }

    public function getProjectId(): int
    {
        return $this->data['project_id'];
    }

    public function setProjectId(int $projectId): void
    {
        $this->data['project_id'] = $projectId;
    }

    public function setTrackerId(int $trackerId): void
    {
        $this->data['tracker_id'] = $trackerId;
    }

    public function setAssignedToId(?int $assignedToId): void
    {
        if (null !== $assignedToId) {
            $this->data['assigned_to_id'] = $assignedToId;
        }
    }

    public function setPriorityId(?int $priorityId): void
    {
        if (null !== $priorityId) {
            $this->data['priority_id'] = $priorityId;
        }
    }

    public function setCategoryId(?int $categoryId): void
    {
        if (null !== $categoryId) {
            $this->data['category_id'] = $categoryId;
        }
    }

    public function setFixedVersionId(?int $fixedVersionId): void
    {
        if (null !== $fixedVersionId) {
            $this->data['fixed_version_id'] = $fixedVersionId;
        }
    }

    public function setStartDate(?string $startDate): void
    {
        if (null !== $startDate && '' !== $startDate) {
            $this->data['start_date'] = $startDate;
        }
    }

    public function setDueDate(?string $dueDate): void
    {
        if (null !== $dueDate && '' !== $dueDate) {
            $this->data['due_date'] = $dueDate;
        }
    }

    public function setEstimatedHours(?float $estimatedHours): void
    {
        if (null !== $estimatedHours) {
            $this->data['estimated_hours'] = $estimatedHours;
        }
    }
}
