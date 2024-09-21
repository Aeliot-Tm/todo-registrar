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
        return new IssueService(
            $this->createGithubClient()->api('issue'),
            $this->config['owner'],
            $this->config['repository'],
        );
    }

    public function createLabelService(): LabelService
    {
        return new LabelService(
            $this->createGithubClient()->api('issue')->labels(),
            $this->config['owner'],
            $this->config['repository'],
        );
    }

    /**
     * @return Authentication
     */
    private function createAuthenticationPlugin(): Authentication
    {
        return new Authentication(
            $this->config['personalAccessToken'],
            null,
            AuthMethod::JWT,
        );
    }

    /**
     * @return GithubClient
     */
    private function createGithubClient(): GithubClient
    {
        $httpClientBuilder = new Builder();
        $httpClientBuilder->addPlugin($this->createAuthenticationPlugin());

        return new GithubClient($httpClientBuilder);
    }
}
