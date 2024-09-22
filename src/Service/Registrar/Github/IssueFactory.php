<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

use Aeliot\TodoRegistrar\Dto\Registrar\Todo;

final class IssueFactory
{
    public function __construct(private IssueConfig $issueConfig)
    {
    }

    public function create(Todo $todo): Issue
    {
        $issue = new Issue();
        $issue->setTitle($todo->getSummary());
        $issue->setBody($todo->getDescription());

        $this->setAssignees($issue, $todo);
        $this->setLabels($issue, $todo);

        return $issue;
    }

    private function setAssignees(Issue $issue, Todo $todo): void
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

    private function setLabels(Issue $issue, Todo $todo): void
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
