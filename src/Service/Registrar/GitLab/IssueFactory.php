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

use Aeliot\TodoRegistrar\Contracts\TodoInterface;

final class IssueFactory
{
    public function __construct(
        private IssueConfig $issueConfig,
        private UserResolver $userResolver,
        private MilestoneApiClient $milestoneApiClient,
    ) {
    }

    public function create(TodoInterface $todo): Issue
    {
        $issue = new Issue();
        $issue->setTitle($this->issueConfig->getSummaryPrefix() . $todo->getSummary());
        $issue->setDescription($todo->getDescription());

        $this->setAssignees($issue, $todo);
        $this->setLabels($issue, $todo);
        $this->setMilestone($issue, $todo);
        $this->setDueDate($issue, $todo);

        return $issue;
    }

    private function setAssignees(Issue $issue, TodoInterface $todo): void
    {
        // Collect assignees from all sources: inline config, tag assignee, global config
        $assignees = array_filter([
            $todo->getAssignee(),
            ...((array) ($todo->getInlineConfig()['assignee'] ?? [])),
            ...$this->issueConfig->getAssignee(),
        ], static fn ($value): bool => '' !== (string) $value);

        if (!$assignees) {
            return;
        }

        // Convert username/email to user IDs
        $assigneeIds = $this->userResolver->resolveUserIds($assignees);

        $issue->setAssigneeIds($assigneeIds);
    }

    private function setLabels(Issue $issue, TodoInterface $todo): void
    {
        $labels = [
            ...(array) ($todo->getInlineConfig()['labels'] ?? []),
            ...$this->issueConfig->getLabels(),
        ];

        if ($this->issueConfig->isAddTagToLabels()) {
            $labels[] = strtolower(\sprintf('%s%s', $this->issueConfig->getTagPrefix(), $todo->getTag()));
        }

        $issue->setLabels(array_unique($labels));
    }

    private function setMilestone(Issue $issue, TodoInterface $todo): void
    {
        $milestone = array_values(array_filter([
            $todo->getInlineConfig()['milestone'] ?? null,
            $this->issueConfig->getMilestone(),
        ]))[0] ?? null;

        if (null === $milestone) {
            return;
        }

        // If numeric, try to find by ID first, then by IID
        if (\is_int($milestone) || ctype_digit((string) $milestone)) {
            $numericValue = (int) $milestone;
            // First try to find by ID
            if ($this->milestoneApiClient->findById($numericValue)) {
                $milestoneId = $numericValue;
            } else {
                // If not found by ID, try to find by IID
                $milestoneId = $this->milestoneApiClient->findByIid($numericValue);
            }
        } else {
            // If string, search by title
            $milestoneId = $this->milestoneApiClient->findByTitle((string) $milestone);
        }

        if (null !== $milestoneId) {
            $issue->setMilestoneId($milestoneId);
        }
    }

    private function setDueDate(Issue $issue, TodoInterface $todo): void
    {
        $dueDate = $todo->getInlineConfig()['due_date']
            ?? $this->issueConfig->getDueDate();

        if (null !== $dueDate) {
            $issue->setDueDate((string) $dueDate);
        }
    }
}
