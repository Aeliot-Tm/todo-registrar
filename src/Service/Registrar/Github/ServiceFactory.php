<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

use Github\AuthMethod;
use Github\Client as GithubClient;
use Github\HttpClient\Builder;
use Github\HttpClient\Plugin\Authentication;

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
        $httpClientBuilder = new Builder();
        $httpClientBuilder->addPlugin(new Authentication(
            $this->config['personalAccessToken'],
            null,
            AuthMethod::JWT,
        ));
        $client = new GithubClient($httpClientBuilder);

        return new IssueService($client->api('issue'), $this->config['owner'], $this->config['repository']);
    }
}
