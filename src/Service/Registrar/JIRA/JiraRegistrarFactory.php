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

use Aeliot\TodoRegistrar\Contracts\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Contracts\RegistrarInterface;

/**
 * TODO: #145 make assertion of JIRA config with symfony/validator component.
 */
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
