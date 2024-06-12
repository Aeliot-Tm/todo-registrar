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
            ->setIssueTypeAsString($this->issueConfig->getIssueType())
            ->setSummary($todo->getSummary())
            ->setDescription($todo->getDescription())
            ->addComponentsAsArray($this->issueConfig->getComponents());

        $assignee = $todo->getAssignee();
        if ($assignee) {
            $issueField->setAssigneeNameAsString($assignee);
        }

        $labels = $this->issueConfig->getLabels();
        if ($this->issueConfig->addTagToLabels()) {
            $labels[] = strtolower(sprintf('%s%s', $this->issueConfig->getTagPrefix(), $todo->getTag()));
        }

        foreach ($labels as $label) {
            $issueField->addLabelAsString($label);
        }

        return $issueField;
    }
}
