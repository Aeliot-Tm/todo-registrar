<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;

final class GithubRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueFactory $issueFactory,
        private ServiceFactory $serviceFactory,
    ) {
    }

    public function isRegistered(Todo $todo): bool
    {
        return (bool) preg_match('/^\\s*\\b#\\d+\\b/i', $todo->getSummary());
    }

    public function register(Todo $todo): string
    {
        $params = $this->issueFactory->create($todo);

        if ($params->getLabels()) {
            $this->registerLabels($params->getLabels());
        }

        $response = $this->serviceFactory->createIssueService()->create($params);

        return (string) $response['number'];
    }

    /**
     * @param string[] $labels
     */
    private function registerLabels(array $labels): void
    {
        // TODO create labels when not exists
        // https://github.com/KnpLabs/php-github-api/blob/master/doc/issue/labels.md
    }
}
