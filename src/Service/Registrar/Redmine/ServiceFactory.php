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

namespace Aeliot\TodoRegistrar\Service\Registrar\Redmine;

use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Exception\LogicException;
use GuzzleHttp\Client as GuzzleClient;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Redmine\Client\Client;
use Redmine\Client\Psr18Client;

/**
 * @internal
 */
final readonly class ServiceFactory
{
    /**
     * @param array<string,mixed> $config
     */
    public function __construct(
        private array $config,
    ) {
    }

    /**
     * @throws InvalidConfigException
     * @throws LogicException
     */
    public function createClient(): Client
    {
        $url = $this->getUrl();
        try {
            return new Psr18Client(
                new GuzzleClient(['http_errors' => true]),
                new RequestFactory(),
                new StreamFactory(),
                $url,
                $this->config['apikeyOrUsername'],
                $this->config['password'] ?? null,
            );
        } catch (\Exception $exception) {
            // not reachable statement but leave it here
            throw new LogicException('Cannot create Redmine service', 0, $exception);
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function getUrl(): string
    {
        $url = $this->config['url'] ?? null;
        if (empty($url)) {
            throw new InvalidConfigException('Redmine URL must be specified in service config');
        }

        return rtrim((string) $url, '/');
    }
}
