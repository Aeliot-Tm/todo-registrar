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

namespace Aeliot\TodoRegistrar\Service\Registrar\GitLab;

use Gitlab\Client;

final class ApiClientProvider
{
    private ?Client $client = null;
    private ?IssueApiClient $issueApiClient = null;
    private ?LabelApiClient $labelApiClient = null;
    private ?MilestoneApiClient $milestoneApiClient = null;
    private ?UserResolver $userResolver = null;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    public function getIssueService(): IssueApiClient
    {
        if ($this->issueApiClient) {
            return $this->issueApiClient;
        }

        $projectIdentifier = $this->getProjectIdentifier();

        return $this->issueApiClient = new IssueApiClient(
            $this->createGitlabClient()->issues(),
            $projectIdentifier,
        );
    }

    public function getLabelService(): LabelApiClient
    {
        if ($this->labelApiClient) {
            return $this->labelApiClient;
        }

        $projectIdentifier = $this->getProjectIdentifier();

        return $this->labelApiClient = new LabelApiClient(
            $this->createGitlabClient(),
            $projectIdentifier,
        );
    }

    public function getMilestoneService(): MilestoneApiClient
    {
        if ($this->milestoneApiClient) {
            return $this->milestoneApiClient;
        }

        $projectIdentifier = $this->getProjectIdentifier();

        return $this->milestoneApiClient = new MilestoneApiClient(
            $this->createGitlabClient(),
            $projectIdentifier,
        );
    }

    public function getUserResolver(): UserResolver
    {
        if ($this->userResolver) {
            return $this->userResolver;
        }

        return $this->userResolver = new UserResolver($this->createGitlabClient());
    }

    private function getProjectIdentifier(): int|string
    {
        // Either path or ID
        $projectIdentifier = $this->config['project'] ?? null;
        if ('' === (string) $projectIdentifier) {
            throw new \InvalidArgumentException('Project identifier must be specified in service config');
        }

        // If already an integer, return as is
        if (\is_int($projectIdentifier) || ctype_digit((string) $projectIdentifier)) {
            return (int) $projectIdentifier;
        }

        // Otherwise, it's a project path (return as string, API will URL-encode it)
        return $projectIdentifier;
    }

    private function createGitlabClient(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $client = new Client();

        // Set URL (default to gitlab.com, or use provided host)
        $url = $this->config['host'] ?? 'https://gitlab.com';
        // Library automatically adds /api/v4, so we need base URL without it
        // Remove trailing slash and /api/v4 if present
        $url = preg_replace('#/api/v4?$#', '', rtrim($url, '/'));
        $client->setUrl($url);

        $this->authenticate($client);

        return $this->client = $client;
    }

    private function authenticate(Client $client): void
    {
        $methodToTokenField = [
            Client::AUTH_OAUTH_TOKEN => 'oauthToken',
            Client::AUTH_HTTP_TOKEN => 'personalAccessToken',
        ];

        foreach ($methodToTokenField as $authMethod => $field) {
            $token = $this->config[$field] ?? null;
            if ($token) {
                $client->authenticate($token, $authMethod);

                return;
            }
        }

        throw new \InvalidArgumentException('Undefined authentication token');
    }
}
