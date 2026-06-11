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

namespace Aeliot\TodoRegistrar\Test\Unit\Service;

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Service\File\Finder;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryRegistry;
use Aeliot\TodoRegistrar\Service\RegistrarProvider;
use Aeliot\TodoRegistrar\Service\ValidatorFactory;
use Aeliot\TodoRegistrar\Test\Stub\StaticRegistrarFactory;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\Todo\TodoInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegistrarProvider::class)]
final class RegistrarProviderTest extends TestCase
{
    private RegistrarProvider $registrarProvider;

    protected function setUp(): void
    {
        $registry = $this->createMock(RegistrarFactoryRegistry::class);
        $this->registrarProvider = new RegistrarProvider($registry, ValidatorFactory::create());
    }

    public function testGetRegistrarWithLegacyFactoryInstance(): void
    {
        $factory = new StaticRegistrarFactory();
        $config = $this->createConfig($factory, ['ticket_key' => 'LEGACY-1']);

        $registrar = $this->registrarProvider->getRegistrar($config->getRegistrarType(), $config->getRegistrarConfig());

        self::assertRegistrarReturnsKey($registrar, 'LEGACY-1');
    }

    private static function assertRegistrarReturnsKey(
        RegistrarInterface $registrar,
        string $expectedKey,
    ): void {
        $todo = self::createStub(TodoInterface::class);
        self::assertSame($expectedKey, $registrar->register($todo));
    }

    /**
     * @param class-string<RegistrarFactoryInterface>|RegistrarFactoryInterface $registrarType
     * @param array<string, mixed> $registrarConfig
     */
    private function createConfig(
        string|RegistrarFactoryInterface $registrarType,
        array $registrarConfig,
    ): Config {
        return (new Config())
            ->setFinder((new Finder())->name('/\.(?:php|yaml|yml)$/')->in(__DIR__))
            ->setRegistrar($registrarType, $registrarConfig);
    }
}
