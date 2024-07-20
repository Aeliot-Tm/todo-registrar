<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;

class JiraRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueFieldFactory $issueFieldFactory,
        private ServiceFactory $serviceFactory,
        private IssueLinkRegistrar $issueLinkRegistrar,
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
        $this->issueLinkRegistrar->registerLinks($issueKey, $todo);

        return $issueKey;
    }
}
