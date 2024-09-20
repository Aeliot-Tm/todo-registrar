<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

final class ServiceFactory
{
    /**
     * @param array<string,mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    public function createIssueService(): IssueService
    {
        $client = new \Github\Client();
        // TODO add authentication

        return new IssueService($client->api('issue'), $this->config['owner'], $this->config['repository']);
    }
}
