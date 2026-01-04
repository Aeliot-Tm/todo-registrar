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

/**
 * @see https://github.com/lesstif/php-jira-rest-client?tab=readme-ov-file#use-array
 *
 * @internal
 */
final readonly class IssueServiceArrayConfigPreparer
{
    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function prepare(array $config): array
    {
        $serviceConfig = [
            'jiraHost' => $config['host'],
            'useTokenBasedAuth' => $config['tokenBasedAuth'] ?? false,
            'jiraLogEnabled' => $config['logEnabled'] ?? false,
            'cookieAuthEnabled' => $config['cookieAuthEnabled'] ?? false,
        ];

        $this->addAuthOptions($serviceConfig, $config);
        $this->addCookieOptions($serviceConfig, $config);
        $this->addCurlOptions($serviceConfig, $config);
        $this->addLogOptions($serviceConfig, $config);
        $this->addProxyOptions($serviceConfig, $config);

        if (isset($config['serviceDeskId'])) {
            $serviceConfig['serviceDeskId'] = $config['serviceDeskId'];
        }

        return $serviceConfig;
    }

    /**
     * @param array<string, mixed> $serviceConfig
     * @param array<string, mixed> $config
     */
    private function addAuthOptions(array &$serviceConfig, array $config): void
    {
        if ($serviceConfig['useTokenBasedAuth']) {
            $serviceConfig['personalAccessToken'] = $config['personalAccessToken'];
        } else {
            $serviceConfig['jiraUser'] = $config['jiraUser'];
            $serviceConfig['jiraPassword'] = $config['jiraPassword'];
        }
    }

    /**
     * @param array<string, mixed> $serviceConfig
     * @param array<string, mixed> $config
     */
    private function addCookieOptions(array &$serviceConfig, array $config): void
    {
        if ($serviceConfig['cookieAuthEnabled']) {
            $serviceConfig['cookieFile'] = $config['cookieFile'];
        }
    }

    /**
     * @param array<string, mixed> $serviceConfig
     * @param array<string, mixed> $config
     */
    private function addCurlOptions(array &$serviceConfig, array $config): void
    {
        $fields = [
            'sslVerifyHost',
            'sslVerifyPeer',
            'sslCert',
            'sslCertPassword',
            'sslKey',
            'sslKeyPassword',
            'verbose',
            'userAgent',
        ];

        $options = array_intersect_key($config['curl'] ?? [], array_flip($fields));
        $options = array_filter($options, static fn (mixed $x): bool => isset($x));
        $keys = array_map(static fn (string $x): string => 'curlOpt' . ucfirst($x), array_keys($options));

        $serviceConfig += array_combine($keys, $options);
    }

    /**
     * @param array<string, mixed> $serviceConfig
     * @param array<string, mixed> $config
     */
    private function addLogOptions(array &$serviceConfig, array $config): void
    {
        if ($serviceConfig['jiraLogEnabled']) {
            $serviceConfig['jiraLogFile'] = $config['logFile'];
            $serviceConfig['jiraLogLevel'] = $config['logLevel'] ?? 'WARNING';
        }
    }

    /**
     * @param array<string, mixed> $serviceConfig
     * @param array<string, mixed> $config
     */
    private function addProxyOptions(array &$serviceConfig, array $config): void
    {
        if ($config['proxyEnabled'] ?? false) {
            $serviceConfig['proxyServer'] = $config['proxyServer'];
            $serviceConfig['proxyPort'] = $config['proxyPort'];
            $serviceConfig['proxyUser'] = $config['proxyUser'];
            $serviceConfig['proxyPassword'] = $config['proxyPassword'];
        }
    }
}
