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
use Aeliot\TodoRegistrar\Test\Stub\LegacyConfig;
use Aeliot\TodoRegistrar\Test\Stub\NewStaticRegistrarFactory;
use Aeliot\TodoRegistrar\Test\Stub\StaticRegistrarFactory;
use Aeliot\TodoRegistrarContracts\GeneralConfigInterface as LegacyGeneralConfigInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarFactoryInterface as NewRegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface as NewRegistrarInterface;
use Aeliot\TodoRegistrarContracts\RegistrarFactoryInterface as LegacyRegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\RegistrarInterface as LegacyRegistrarInterface;
use Aeliot\TodoRegistrarContracts\Todo\TodoInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @return iterable<string, array{0: class-string<NewRegistrarFactoryInterface|LegacyRegistrarFactoryInterface>}>
     */
    public static function customRegistrarFactoryClassProvider(): iterable
    {
        yield 'legacy factory interface' => [StaticRegistrarFactory::class];
        yield 'new factory interface' => [NewStaticRegistrarFactory::class];
    }

    #[DataProvider('customRegistrarFactoryClassProvider')]
    public function testGetRegistrarWithCustomFactoryClass(string $factoryClass): void
    {
        $config = $this->createConfig($factoryClass, ['ticket_key' => 'TEST-100']);

        $registrar = $this->registrarProvider->getRegistrar($config);

        self::assertRegistrarReturnsKey($registrar, 'TEST-100');
    }

    public function testGetRegistrarWithLegacyFactoryInstance(): void
    {
        $factory = new StaticRegistrarFactory();
        $config = $this->createConfig($factory, ['ticket_key' => 'LEGACY-1']);

        $registrar = $this->registrarProvider->getRegistrar($config);

        self::assertRegistrarReturnsKey($registrar, 'LEGACY-1');
    }

    public function testGetRegistrarWithNewFactoryInstance(): void
    {
        $factory = new NewStaticRegistrarFactory();
        $config = $this->createConfig($factory, ['ticket_key' => 'NEW-1']);

        $registrar = $this->registrarProvider->getRegistrar($config);

        self::assertRegistrarReturnsKey($registrar, 'NEW-1');
    }

    public function testGetRegistrarWithLegacyGeneralConfig(): void
    {
        $config = (new LegacyConfig())
            ->setFinder((new Finder())->in(__DIR__))
            ->setRegistrar(StaticRegistrarFactory::class, ['ticket_key' => 'LEGACY-CFG-1']);

        self::assertInstanceOf(LegacyGeneralConfigInterface::class, $config);

        $registrar = $this->registrarProvider->getRegistrar($config);

        self::assertRegistrarReturnsKey($registrar, 'LEGACY-CFG-1');
    }

    private static function assertRegistrarReturnsKey(
        LegacyRegistrarInterface|NewRegistrarInterface $registrar,
        string $expectedKey,
    ): void {
        $todo = self::createStub(TodoInterface::class);
        self::assertSame($expectedKey, $registrar->register($todo));
    }

    /**
     * @param class-string<NewRegistrarFactoryInterface|LegacyRegistrarFactoryInterface>|NewRegistrarFactoryInterface|LegacyRegistrarFactoryInterface $registrarType
     * @param array<string, mixed> $registrarConfig
     */
    private function createConfig(
        string|NewRegistrarFactoryInterface|LegacyRegistrarFactoryInterface $registrarType,
        array $registrarConfig,
    ): Config {
        return (new Config())
            ->setFinder((new Finder())->in(__DIR__))
            ->setRegistrar($registrarType, $registrarConfig);
    }
}
