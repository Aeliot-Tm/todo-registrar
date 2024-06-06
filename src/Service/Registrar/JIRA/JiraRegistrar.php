<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;
use JiraRestApi\Issue\IssueField;
use JiraRestApi\Issue\IssueService;

final class JiraRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueConfig $issueConfig,
        private IssueService $issueService,
    ) {
    }

    public function isRegistered(CommentPart $commentPart): bool
    {
        $lineWithoutPrefix = substr($commentPart->getFirstLine(), $commentPart->getPrefixLength());

        return preg_match('/^\\s*\\b[A-Z]+-\\d+\\b/i', $lineWithoutPrefix);
    }

    public function register(CommentPart $commentPart): void
    {
        $issueField = $this->createIssueField($commentPart);
        $issue = $this->issueService->create($issueField);
        $commentPart->injectKey($issue->key);
    }

    private function createIssueField(CommentPart $commentPart): IssueField
    {
        $issueField = new IssueField();
        $issueField
            ->setProjectKey($this->issueConfig->getProjectKey())
            ->setIssueTypeAsString($this->issueConfig->getIssueType())
            ->setSummary($commentPart->getFirstLine())
            ->setDescription($commentPart->getContent())
            ->addComponentsAsArray($this->issueConfig->getComponents());

        $assignee = $commentPart->getTagMetadata()?->getAssignee();
        if ($assignee) {
            $issueField->setAssigneeNameAsString($assignee);
        }

        $labels = $this->issueConfig->getLabels();
        if ($this->issueConfig->addTagToLabels()) {
            $labels[] = strtolower(sprintf('%s%s', $this->issueConfig->getTagPrefix(), $commentPart->getTag()));
        }

        foreach ($labels as $label) {
            $issueField->addLabelAsString($label);
        }

        return $issueField;
    }
}