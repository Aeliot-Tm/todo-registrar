<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use JiraRestApi\Issue\IssueField;

final class IssueFieldFactory
{
    public function __construct(
        private IssueConfig $issueConfig,
    ) {
    }

    public function create(Todo $todo): IssueField
    {
        $issueField = new IssueField();
        $issueField
            ->setProjectKey($this->issueConfig->getProjectKey())
            ->setSummary($todo->getSummary())
            ->setDescription($todo->getDescription());

        $this->setIssueType($issueField, $todo);
        $this->setAssignee($issueField, $todo);
        $this->setComponents($issueField, $todo);
        $this->setLabels($issueField, $todo);
        $this->setPriority($issueField, $todo);

        return $issueField;
    }

    private function setAssignee(IssueField $issueField, Todo $todo): void
    {
        $assignee = $todo->getInlineConfig()['priority']
            ?? $todo->getAssignee()
            ?? $this->issueConfig->getAssignee();

        if ($assignee) {
            $issueField->setAssigneeNameAsString($assignee);
        }
    }

    private function setComponents(IssueField $issueField, Todo $todo): void
    {
        $component = [
            ...($todo->getInlineConfig()['components'] ?? []),
            ...$this->issueConfig->getComponents(),
        ];
        $issueField->addComponentsAsArray(array_unique($component));
    }

    private function setIssueType(IssueField $issueField, Todo $todo): void
    {
        $inlineConfig = $todo->getInlineConfig();
        $issueType = $inlineConfig['issue_type']
            ?? $this->issueConfig->getIssueType();

        $issueField->setIssueTypeAsString($issueType);
    }

    private function setLabels(IssueField $issueField, Todo $todo): void
    {
        $labels = [
            ...($todo->getInlineConfig()['labels'] ?? []),
            ...$this->issueConfig->getLabels(),
        ];

        if ($this->issueConfig->isAddTagToLabels()) {
            $labels[] = strtolower(sprintf('%s%s', $this->issueConfig->getTagPrefix(), $todo->getTag()));
        }

        foreach (array_unique($labels) as $label) {
            $issueField->addLabelAsString($label);
        }
    }

    private function setPriority(IssueField $issueField, Todo $todo): void
    {
        $priority = $todo->getInlineConfig()['priority']
            ?? $this->issueConfig->getPriority();

        if ($priority) {
            $issueField->setPriorityNameAsString($priority);
        }
    }
}
