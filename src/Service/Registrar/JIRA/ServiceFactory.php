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

use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\IssueLink\IssueLinkService;

/**
 * @internal
 */
final readonly class ServiceFactory
{
    /**
     * @param array<string,mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    public function createIssueLinkService(): IssueLinkService
    {
        return new IssueLinkService($this->getServiceConfig());
    }

    public function createIssueService(): IssueService
    {
        return new IssueService($this->getServiceConfig());
    }

    private function getServiceConfig(): ArrayConfiguration
    {
        $serviceConfig = (new IssueServiceArrayConfigPreparer())->prepare($this->config);

        return new ArrayConfiguration($serviceConfig);
    }
}
