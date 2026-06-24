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

use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Field\FieldService;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\IssueLink\IssueLinkService;
use JiraRestApi\JiraException;

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

    /**
     * @throws InvalidConfigException
     */
    public function createFieldService(): FieldService
    {
        $serviceConfig = $this->getServiceConfig();

        try {
            return new FieldService($serviceConfig);
        } catch (JiraException $exception) {
            throw new InvalidConfigException('Cannot create JIRA field service', 0, $exception);
        }
    }

    /**
     * @throws InvalidConfigException
     */
    public function createIssueLinkService(): IssueLinkService
    {
        $serviceConfig = $this->getServiceConfig();

        try {
            return new IssueLinkService($serviceConfig);
        } catch (JiraException $exception) {
            throw new InvalidConfigException('Cannot create JIRA issue link service', 0, $exception);
        }
    }

    /**
     * @throws InvalidConfigException
     */
    public function createIssueService(): IssueService
    {
        $serviceConfig = $this->getServiceConfig();

        try {
            return new IssueService($serviceConfig);
        } catch (JiraException $exception) {
            throw new InvalidConfigException('Cannot create JIRA issue service', 0, $exception);
        }
    }

    private function getServiceConfig(): ArrayConfiguration
    {
        $serviceConfig = (new IssueServiceArrayConfigPreparer())->prepare($this->config);

        return new ArrayConfiguration($serviceConfig);
    }
}
