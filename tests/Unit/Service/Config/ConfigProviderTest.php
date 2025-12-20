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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Config;

use Aeliot\TodoRegistrar\Contracts\GeneralConfigInterface;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Service\Config\AbsolutePathMaker;
use Aeliot\TodoRegistrar\Service\Config\ArrayConfigFactory;
use Aeliot\TodoRegistrar\Service\Config\ConfigFactory;
use Aeliot\TodoRegistrar\Service\Config\ConfigFileDetector;
use Aeliot\TodoRegistrar\Service\Config\ConfigFileGuesser;
use Aeliot\TodoRegistrar\Service\Config\ConfigProvider;
use Aeliot\TodoRegistrar\Service\Config\StdinConfigFactory;
use Aeliot\TodoRegistrar\Service\ValidatorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(ConfigProvider::class)]
final class ConfigProviderTest extends TestCase
{
    private ConfigProvider $configProvider;
    private static ValidatorInterface $validator;

    public static function setUpBeforeClass(): void
    {
        self::$validator = ValidatorFactory::create();
    }

    protected function setUp(): void
    {
        $absolutePathMaker = new AbsolutePathMaker();
        $configFileDetector = new ConfigFileDetector(
            $absolutePathMaker,
            new ConfigFileGuesser($absolutePathMaker),
        );
        $arrayConfigFactory = new ArrayConfigFactory(self::$validator);
        $configFactory = new ConfigFactory($arrayConfigFactory);
        $stdinConfigFactory = new StdinConfigFactory($arrayConfigFactory);

        $this->configProvider = new ConfigProvider(
            $configFileDetector,
            $configFactory,
            $stdinConfigFactory,
            self::$validator,
        );
    }

    public function testLoadValidPhpConfig(): void
    {
        $path = __DIR__ . '/../../../fixtures/config/valid_config.php';

        $config = $this->configProvider->getConfig($path);

        self::assertSame(RegistrarType::GitHub, $config->getRegistrarType());
        self::assertSame(['todo'], $config->getTags());
    }

    public function testLoadYamlConfig(): void
    {
        $path = __DIR__ . '/../../../fixtures/config/simple_config.yaml';

        $config = $this->configProvider->getConfig($path);

        self::assertSame('App\RegistrarFactory', $config->getRegistrarType());
        self::assertSame(['my_tag'], $config->getTags());
    }

    public static function getDataForTestInvalidPhpConfigType(): iterable
    {
        yield 'returns array' => [
            'invalid_config_returns_array.php',
            'array',
        ];

        yield 'returns string' => [
            'invalid_config_returns_string.php',
            'string',
        ];

        yield 'returns null' => [
            'invalid_config_returns_null.php',
            'null',
        ];
    }

    #[DataProvider('getDataForTestInvalidPhpConfigType')]
    public function testInvalidPhpConfigTypeThrowsException(string $filename, string $expectedType): void
    {
        $path = __DIR__ . '/../../../fixtures/config/' . $filename;

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('must return an instance of ' . GeneralConfigInterface::class);
        $this->expectExceptionMessage('got ' . $expectedType);

        $this->configProvider->getConfig($path);
    }

    public function testMissingFinderThrowsValidationException(): void
    {
        $path = __DIR__ . '/../../../fixtures/config/invalid_config_missing_finder.php';

        $this->expectException(ConfigValidationException::class);

        try {
            $this->configProvider->getConfig($path);
        } catch (ConfigValidationException $e) {
            $messages = $e->getErrorMessages();
            self::assertCount(1, $messages);
            self::assertStringContainsString('finder', $messages[0]);
            self::assertStringContainsString('required', $messages[0]);
            throw $e;
        }
    }

    public function testMissingRegistrarThrowsValidationException(): void
    {
        $path = __DIR__ . '/../../../fixtures/config/invalid_config_missing_registrar.php';

        $this->expectException(ConfigValidationException::class);

        try {
            $this->configProvider->getConfig($path);
        } catch (ConfigValidationException $e) {
            $messages = $e->getErrorMessages();
            self::assertCount(1, $messages);
            self::assertStringContainsString('registrar', $messages[0]);
            self::assertStringContainsString('required', $messages[0]);
            throw $e;
        }
    }
}
