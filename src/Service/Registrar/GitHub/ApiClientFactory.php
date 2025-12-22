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

namespace Aeliot\TodoRegistrar\Service\Registrar\GitHub;

use Github\AuthMethod;
use Github\Client as GithubClient;
use Github\HttpClient\Builder;
use Github\HttpClient\Plugin\Authentication;

final readonly class ApiClientFactory
{
    /**
     * @param array<string,mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    public function createIssueApiClient(): IssueApiClient
    {
        return new IssueApiClient(
            $this->createGithubClient()->api('issue'),
            $this->config['owner'],
            $this->config['repository'],
        );
    }

    public function createLabelApiClient(): LabelApiClient
    {
        return new LabelApiClient(
            $this->createGithubClient()->api('issue')->labels(),
            $this->config['owner'],
            $this->config['repository'],
        );
    }

    private function createAuthenticationPlugin(): Authentication
    {
        return new Authentication(
            $this->config['personalAccessToken'],
            null,
            AuthMethod::JWT,
        );
    }

    private function createGithubClient(): GithubClient
    {
        $httpClientBuilder = new Builder();
        $httpClientBuilder->addPlugin($this->createAuthenticationPlugin());

        return new GithubClient($httpClientBuilder);
    }
}
