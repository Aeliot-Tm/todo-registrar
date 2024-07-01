<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;

class JiraRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config): RegistrarInterface
    {
        $issueConfig = ($config['issue'] ?? []) + ['projectKey' => $config['projectKey']];
        $defaultIssueLinkType = $config['issueLinkType'] ?? 'Relates';

        $serviceFactory = new ServiceFactory($config['service']);

        return new JiraRegistrar(
            new IssueFieldFactory(new IssueConfig($issueConfig)),
            $serviceFactory,
            new IssueLinkRegistrar(
                new LinkedIssueNormalizer(
                    $defaultIssueLinkType,
                    new IssueLinkTypeProvider($serviceFactory)
                ),
                $serviceFactory,
            ),
        );
    }
}
