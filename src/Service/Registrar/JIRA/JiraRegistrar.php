<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\Exception\InvalidInlineConfigFormatException;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;
use JiraRestApi\IssueLink\IssueLink;

class JiraRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueFieldFactory $issueFieldFactory,
        private ServiceFactory $serviceFactory,
    ) {
    }

    public function isRegistered(Todo $todo): bool
    {
        return (bool) preg_match('/^\\s*\\b[A-Z]+-\\d+\\b/i', $todo->getSummary());
    }

    public function register(Todo $todo): string
    {
        $issueField = $this->issueFieldFactory->create($todo);

        $issueKey = $this->serviceFactory->createIssueService()->create($issueField)->key;
        $this->registerLinks($issueKey, $todo);

        return $issueKey;
    }

    private function registerLinks(string $inwardIssueKey, Todo $todo): void
    {
        $linkedIssues = $todo->getInlineConfig()['linked_issues'] ?? [];
        if (!$linkedIssues) {
            return;
        }

        if (!array_is_list($linkedIssues)
            || array_reduce($linkedIssues, static fn (mixed $x): int => (int) !is_string($x), $linkedIssues, 0) > 0
        ) {
            throw new InvalidInlineConfigFormatException('List of liked issues must be indexed array of strings');
        }

        $service = $this->serviceFactory->createIssueLinkService();

        foreach ($linkedIssues as $linkedIssue) {
            $issueLink = (new IssueLink())
                ->setInwardIssue($inwardIssueKey)
                ->setOutwardIssue($linkedIssue)
                ->setLinkTypeName('Relates');

            $service->addIssueLink($issueLink);
        }
    }
}
