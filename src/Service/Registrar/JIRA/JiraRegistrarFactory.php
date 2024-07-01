<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\IssueService;

class JiraRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config): RegistrarInterface
    {
        $issueConfig = ($config['issue'] ?? []) + ['projectKey' => $config['projectKey']];
        return new JiraRegistrar(
            new IssueFieldFactory(new IssueConfig($issueConfig)),
            new ServiceFactory($config['service']),
        );
    }
}
