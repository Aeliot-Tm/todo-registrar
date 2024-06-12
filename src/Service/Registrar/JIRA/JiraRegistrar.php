<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;
use JiraRestApi\Issue\IssueField;
use JiraRestApi\Issue\IssueService;

class JiraRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueConfig $issueConfig,
        private IssueService $issueService,
    ) {
    }

    public function isRegistered(Todo $todo): bool
    {
        return preg_match('/^\\s*\\b[A-Z]+-\\d+\\b/i', $todo->getSummary());
    }

    public function register(Todo $todo): string
    {
        $issueField = $this->createIssueField($todo);

        return $this->issueService->create($issueField)->key;
    }

    private function createIssueField(Todo $todo): IssueField
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