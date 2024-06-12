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
        return new JiraRegistrar(
            new IssueFieldFactory(new IssueConfig($config)),
            $this->createIssueService($config['service']),
        );
    }

    private function createIssueService(array $config): IssueService
    {
        $serviceConfig = (new IssueServiceArrayConfigPreparer())->prepare($config);

        return new IssueService(new ArrayConfiguration($serviceConfig));
    }
}