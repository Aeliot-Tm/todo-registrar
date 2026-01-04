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

use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
use Aeliot\TodoRegistrarContracts\TodoInterface;

/**
 * @internal
 */
final readonly class IssueFactory
{
    public function __construct(
        private GeneralIssueConfig $generalIssueConfig,
        private IssueSupporter $issueSupporter,
        private MilestoneApiClient $milestoneApiClient,
        private UserResolver $userResolver,
    ) {
    }

    public function create(TodoInterface $todo): Issue
    {
        $issue = new Issue();
        $issue->setProject($todo->getInlineConfig()['project'] ?? $this->generalIssueConfig->getProject());
        $issue->setTitle($this->issueSupporter->getSummary($todo, $this->generalIssueConfig));
        $issue->setDescription($todo->getDescription());

        $this->setAssignees($issue, $todo);
        $this->setLabels($issue, $todo);
        $this->setMilestone($issue, $todo);
        $this->setDueDate($issue, $todo);

        return $issue;
    }

    private function setAssignees(Issue $issue, TodoInterface $todo): void
    {
        $assignees = $this->issueSupporter->getAssignees($todo, $this->generalIssueConfig);
        if (!$assignees) {
            return;
        }

        $issue->setAssigneeIds($this->userResolver->resolveUserIds($assignees));
    }

    private function setDueDate(Issue $issue, TodoInterface $todo): void
    {
        $dueDate = $todo->getInlineConfig()['due_date']
            ?? $this->generalIssueConfig->getDueDate();

        if (null !== $dueDate) {
            $issue->setDueDate((string) $dueDate);
        }
    }

    private function setLabels(Issue $issue, TodoInterface $todo): void
    {
        $issue->setLabels($this->issueSupporter->getLabels($todo, $this->generalIssueConfig));
    }

    private function setMilestone(Issue $issue, TodoInterface $todo): void
    {
        $milestone = array_values(array_filter([
            $todo->getInlineConfig()['milestone'] ?? null,
            $this->generalIssueConfig->getMilestone(),
        ]))[0] ?? null;

        if (null === $milestone) {
            return;
        }

        // If numeric, try to find by ID first, then by IID
        if (\is_int($milestone) || ctype_digit((string) $milestone)) {
            $numericValue = (int) $milestone;
            // First try to find by ID
            if ($this->milestoneApiClient->hasById($issue->getProject(), $numericValue)) {
                $milestoneId = $numericValue;
            } else {
                // If not found by ID, try to find by IID
                $milestoneId = $this->milestoneApiClient->findIdByIid($issue->getProject(), $numericValue);
            }
        } else {
            // If string, search by title
            $milestoneId = $this->milestoneApiClient->findIdByTitle($issue->getProject(), (string) $milestone);
        }

        if (null !== $milestoneId) {
            $issue->setMilestoneId($milestoneId);
        }
    }
}
