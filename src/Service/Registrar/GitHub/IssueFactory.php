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

namespace Aeliot\TodoRegistrar\Service\Registrar\GitHub;

use Aeliot\TodoRegistrar\Contracts\TodoInterface;

/**
 * @internal
 */
final class IssueFactory
{
    public function __construct(private GeneralIssueConfig $generalIssueConfig)
    {
    }

    public function create(TodoInterface $todo): Issue
    {
        $issue = new Issue();
        $issue->setTitle($todo->getSummary());
        $issue->setBody($todo->getDescription());

        $this->setAssignees($issue, $todo);
        $this->setLabels($issue, $todo);

        return $issue;
    }

    private function setAssignees(Issue $issue, TodoInterface $todo): void
    {
        $assignees = array_filter([
            $todo->getAssignee(),
            ...$todo->getInlineConfig()['assignees'] ?? [],
            ...$this->generalIssueConfig->getAssignees(),
        ]);

        foreach ($assignees as $assignee) {
            $issue->addAssignee($assignee);
        }
    }

    private function setLabels(Issue $issue, TodoInterface $todo): void
    {
        $labels = [
            ...(array) ($todo->getInlineConfig()['labels'] ?? []),
            ...$this->generalIssueConfig->getLabels(),
        ];

        if ($this->generalIssueConfig->isAddTagToLabels()) {
            $labels[] = strtolower(\sprintf('%s%s', $this->generalIssueConfig->getTagPrefix(), $todo->getTag()));
        }

        $labels = array_unique($labels);
        if ($allowedLabels = $this->generalIssueConfig->getAllowedLabels()) {
            $labels = array_intersect($labels, $allowedLabels);
        }

        foreach ($labels as $label) {
            $issue->addLabel($label);
        }
    }
}
