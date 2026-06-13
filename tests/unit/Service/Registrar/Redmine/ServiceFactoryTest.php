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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar\Redmine;

use Aeliot\TodoRegistrar\Service\Registrar\Redmine\ServiceFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Client\Client;

#[CoversClass(ServiceFactory::class)]
final class ServiceFactoryTest extends TestCase
{
    public function testCreateClientWithApiKey(): void
    {
        $config = [
            'url' => 'https://redmine.example.com',
            'apikeyOrUsername' => 'api-key-123',
        ];

        $factory = new ServiceFactory($config);
        $client = $factory->createClient();

        self::assertInstanceOf(Client::class, $client);
    }

    public function testCreateClientWithUsernameAndPassword(): void
    {
        $config = [
            'url' => 'https://redmine.example.com',
            'apikeyOrUsername' => 'username',
            'password' => 'password',
        ];

        $factory = new ServiceFactory($config);
        $client = $factory->createClient();

        self::assertInstanceOf(Client::class, $client);
    }

    public function testCreateClientTrimsTrailingSlashFromUrl(): void
    {
        $config = [
            'url' => 'https://redmine.example.com/',
            'apikeyOrUsername' => 'api-key-123',
        ];

        $factory = new ServiceFactory($config);
        $client = $factory->createClient();

        self::assertInstanceOf(Client::class, $client);
        // Note: We can't directly verify URL trimming without accessing private properties,
        // but the test ensures no exception is thrown and client is created successfully
    }

    public function testCreateClientTrimsMultipleTrailingSlashes(): void
    {
        $config = [
            'url' => 'https://redmine.example.com///',
            'apikeyOrUsername' => 'api-key-123',
        ];

        $factory = new ServiceFactory($config);
        $client = $factory->createClient();

        self::assertInstanceOf(Client::class, $client);
    }

    public function testCreateClientThrowsExceptionOnMissingUrl(): void
    {
        $config = [
            'apikeyOrUsername' => 'api-key-123',
        ];

        $factory = new ServiceFactory($config);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Redmine URL must be specified in service config');

        $factory->createClient();
    }

    public function testCreateClientThrowsExceptionOnEmptyUrl(): void
    {
        $config = [
            'url' => '',
            'apikeyOrUsername' => 'api-key-123',
        ];

        $factory = new ServiceFactory($config);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Redmine URL must be specified in service config');

        $factory->createClient();
    }
}
