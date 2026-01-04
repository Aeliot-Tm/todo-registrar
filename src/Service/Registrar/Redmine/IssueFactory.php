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

use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
use Aeliot\TodoRegistrarContracts\TodoInterface;

/**
 * @internal
 */
final readonly class IssueFactory
{
    public function __construct(
        private EntityResolver $entityResolver,
        private GeneralIssueConfig $generalIssueConfig,
        private IssueSupporter $issueSupporter,
        private UserResolver $userResolver,
    ) {
    }

    public function create(TodoInterface $todo): Issue
    {
        $issue = new Issue();
        $issue->setSubject($this->issueSupporter->getSummary($todo, $this->generalIssueConfig));
        $issue->setDescription($todo->getDescription());

        $projectIdentifier = $todo->getInlineConfig()['project'] ?? $this->generalIssueConfig->getProjectIdentifier();
        $projectId = $this->entityResolver->resolveProjectId($projectIdentifier);
        if (null === $projectId) {
            throw new ProjectNotFoundException(\sprintf('Project "%s" not found', $projectIdentifier));
        }
        $issue->setProjectId($projectId);

        $this->setTracker($issue, $todo);
        $this->setAssignee($issue, $todo);
        $this->setPriority($issue, $todo);
        $this->setCategory($issue, $todo);
        $this->setFixedVersion($issue, $todo);
        $this->setStartDate($issue, $todo);
        $this->setDueDate($issue, $todo);
        $this->setEstimatedHours($issue, $todo);

        return $issue;
    }

    private function setAssignee(Issue $issue, TodoInterface $todo): void
    {
        $assignees = $this->issueSupporter->getAssignees($todo, $this->generalIssueConfig);
        if (!$assignees) {
            return;
        }

        $assigneeId = $this->userResolver->resolveUserId(reset($assignees));
        if (null !== $assigneeId) {
            $issue->setAssignedToId($assigneeId);
        }
    }

    private function setCategory(Issue $issue, TodoInterface $todo): void
    {
        $inlineConfig = $todo->getInlineConfig();
        $category = $inlineConfig['category'] ?? $this->generalIssueConfig->getCategory();

        if (null === $category) {
            return;
        }

        $categoryId = $this->entityResolver->resolveCategoryId($category, $issue->getProjectId());
        if (null !== $categoryId) {
            $issue->setCategoryId($categoryId);
        }
    }

    private function setDueDate(Issue $issue, TodoInterface $todo): void
    {
        $dueDate = $todo->getInlineConfig()['due_date']
            ?? $this->generalIssueConfig->getDueDate();

        if (null !== $dueDate) {
            $issue->setDueDate($dueDate);
        }
    }

    private function setEstimatedHours(Issue $issue, TodoInterface $todo): void
    {
        $estimatedHours = $todo->getInlineConfig()['estimated_hours']
            ?? $this->generalIssueConfig->getEstimatedHours();

        if (null !== $estimatedHours) {
            $issue->setEstimatedHours($estimatedHours);
        }
    }

    private function setFixedVersion(Issue $issue, TodoInterface $todo): void
    {
        $inlineConfig = $todo->getInlineConfig();
        $fixedVersion = $inlineConfig['fixed_version'] ?? $this->generalIssueConfig->getFixedVersion();

        if (null === $fixedVersion) {
            return;
        }

        $versionId = $this->entityResolver->resolveVersionId($fixedVersion, $issue->getProjectId());
        if (null !== $versionId) {
            $issue->setFixedVersionId($versionId);
        }
    }

    private function setPriority(Issue $issue, TodoInterface $todo): void
    {
        $inlineConfig = $todo->getInlineConfig();
        $priority = $inlineConfig['priority'] ?? $this->generalIssueConfig->getPriority();

        if (null === $priority) {
            return;
        }

        $priorityId = $this->entityResolver->resolvePriorityId($priority);
        if (null !== $priorityId) {
            $issue->setPriorityId($priorityId);
        }
    }

    private function setStartDate(Issue $issue, TodoInterface $todo): void
    {
        $startDate = $todo->getInlineConfig()['start_date']
            ?? $this->generalIssueConfig->getStartDate();

        if (null !== $startDate) {
            $issue->setStartDate($startDate);
        }
    }

    private function setTracker(Issue $issue, TodoInterface $todo): void
    {
        $inlineConfig = $todo->getInlineConfig();
        $tracker = $inlineConfig['tracker'] ?? $this->generalIssueConfig->getTracker();

        if (null === $tracker) {
            throw new \RuntimeException('Tracker must be specified in config or inline config');
        }

        $trackerId = $this->entityResolver->resolveTrackerId($tracker);
        if (null === $trackerId) {
            throw new \RuntimeException(\sprintf('Tracker "%s" not found', $tracker));
        }

        $issue->setTrackerId($trackerId);
    }
}
