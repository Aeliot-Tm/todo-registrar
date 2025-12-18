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

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Contracts\TodoInterface;
use JiraRestApi\Issue\IssueField;

final class IssueFieldFactory
{
    public function __construct(
        private IssueConfig $issueConfig,
    ) {
    }

    public function create(TodoInterface $todo): IssueField
    {
        $issueField = new IssueField();
        $issueField
            ->setProjectKey($this->issueConfig->getProjectKey())
            ->setSummary($this->issueConfig->getSummaryPrefix() . $todo->getSummary())
            ->setDescription($todo->getDescription());

        $this->setIssueType($issueField, $todo);
        $this->setAssignee($issueField, $todo);
        $this->setComponents($issueField, $todo);
        $this->setLabels($issueField, $todo);
        $this->setPriority($issueField, $todo);

        return $issueField;
    }

    private function setAssignee(IssueField $issueField, TodoInterface $todo): void
    {
        $assignee = $todo->getInlineConfig()['assignee']
            ?? $todo->getAssignee()
            ?? $this->issueConfig->getAssignee();

        if ($assignee) {
            $issueField->setAssigneeNameAsString($assignee);
        }
    }

    private function setComponents(IssueField $issueField, TodoInterface $todo): void
    {
        $component = [
            ...($todo->getInlineConfig()['components'] ?? []),
            ...$this->issueConfig->getComponents(),
        ];
        $issueField->addComponentsAsArray(array_unique($component));
    }

    private function setIssueType(IssueField $issueField, TodoInterface $todo): void
    {
        $inlineConfig = $todo->getInlineConfig();
        $issueType = $inlineConfig['issue_type']
            ?? $this->issueConfig->getIssueType();

        $issueField->setIssueTypeAsString($issueType);
    }

    private function setLabels(IssueField $issueField, TodoInterface $todo): void
    {
        $labels = [
            ...(array) ($todo->getInlineConfig()['labels'] ?? []),
            ...$this->issueConfig->getLabels(),
        ];

        if ($this->issueConfig->isAddTagToLabels()) {
            $labels[] = strtolower(\sprintf('%s%s', $this->issueConfig->getTagPrefix(), $todo->getTag()));
        }

        foreach (array_unique($labels) as $label) {
            $issueField->addLabelAsString($label);
        }
    }

    private function setPriority(IssueField $issueField, TodoInterface $todo): void
    {
        $priority = $todo->getInlineConfig()['priority']
            ?? $this->issueConfig->getPriority();

        if ($priority) {
            $issueField->setPriorityNameAsString($priority);
        }
    }
}
