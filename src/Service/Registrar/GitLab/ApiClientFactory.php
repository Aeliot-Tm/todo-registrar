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

final readonly class ApiClientFactory
{
    /**
     * @param array<string,mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    public function createClient(): Client
    {
        $client = new Client();

        // Set URL (default to gitlab.com, or use provided host)
        $url = $this->config['host'] ?? 'https://gitlab.com';
        // Library automatically adds /api/v4, so we need base URL without it
        // Remove trailing slash and /api/v4 if present
        $url = preg_replace('#/api/v4?$#', '', rtrim($url, '/'));
        $client->setUrl($url);

        $this->authenticate($client);

        return $client;
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
