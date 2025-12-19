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

final class IssueFactory
{
    public function __construct(private IssueConfig $issueConfig)
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
            ...$this->issueConfig->getAssignees(),
        ]);

        foreach ($assignees as $assignee) {
            $issue->addAssignee($assignee);
        }
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

        foreach (array_unique($labels) as $label) {
            $issue->addLabel($label);
        }
    }
}
