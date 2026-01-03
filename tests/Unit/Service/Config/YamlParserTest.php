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

use Aeliot\EnvResolver\Exception\EnvFoundException;
use Aeliot\EnvResolver\Service\StringProcessor;
use Aeliot\TodoRegistrar\Service\Config\YamlParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(YamlParser::class)]
final class YamlParserTest extends TestCase
{
    private YamlParser $yamlParser;

    protected function setUp(): void
    {
        $this->yamlParser = new YamlParser(new StringProcessor());
    }

    public function testParseSimpleYaml(): void
    {
        $yaml = <<<'YAML'
            key: value
            number: 42
            nested:
              child: data
            YAML;

        $result = $this->yamlParser->parse($yaml);

        self::assertSame([
            'key' => 'value',
            'number' => 42,
            'nested' => [
                'child' => 'data',
            ],
        ], $result);
    }

    public function testParseEmptyYaml(): void
    {
        $result = $this->yamlParser->parse('');

        self::assertSame([], $result);
    }

    public function testResolveEnvVar(): void
    {
        $_ENV['TEST_VAR'] = 'resolved_value';

        try {
            $yaml = <<<'YAML'
                token: '%env(TEST_VAR)%'
                YAML;

            $result = $this->yamlParser->parse($yaml);

            self::assertSame(['token' => 'resolved_value'], $result);
        } finally {
            unset($_ENV['TEST_VAR']);
        }
    }

    public function testResolveEnvVarFromGetenv(): void
    {
        putenv('TEST_GETENV_VAR=getenv_value');

        try {
            $yaml = <<<'YAML'
                api_key: '%env(TEST_GETENV_VAR)%'
                YAML;

            $result = $this->yamlParser->parse($yaml);

            self::assertSame(['api_key' => 'getenv_value'], $result);
        } finally {
            putenv('TEST_GETENV_VAR');
        }
    }

    public function testResolveNestedEnvVars(): void
    {
        $_ENV['NESTED_VAR_1'] = 'value1';
        $_ENV['NESTED_VAR_2'] = 'value2';

        try {
            $yaml = <<<'YAML'
                registrar:
                  type: github
                  options:
                    token: '%env(NESTED_VAR_1)%'
                    secret: '%env(NESTED_VAR_2)%'
                YAML;

            $result = $this->yamlParser->parse($yaml);

            self::assertSame([
                'registrar' => [
                    'type' => 'github',
                    'options' => [
                        'token' => 'value1',
                        'secret' => 'value2',
                    ],
                ],
            ], $result);
        } finally {
            unset($_ENV['NESTED_VAR_1'], $_ENV['NESTED_VAR_2']);
        }
    }

    public function testMissingEnvVarThrowsException(): void
    {
        $yaml = <<<'YAML'
            token: '%env(NON_EXISTENT_VAR_12345)%'
            YAML;

        $this->expectException(EnvFoundException::class);
        $this->expectExceptionMessage('Undefined environment variable "NON_EXISTENT_VAR_12345"');

        $this->yamlParser->parse($yaml);
    }

    public function testEnvVarEnvPrefersEnvOverGetenv(): void
    {
        $_ENV['PRIORITY_VAR'] = 'from_env';
        putenv('PRIORITY_VAR=from_getenv');

        try {
            $yaml = <<<'YAML'
                value: '%env(PRIORITY_VAR)%'
                YAML;

            $result = $this->yamlParser->parse($yaml);

            self::assertSame(['value' => 'from_env'], $result);
        } finally {
            unset($_ENV['PRIORITY_VAR']);
            putenv('PRIORITY_VAR');
        }
    }

    public function testNonEnvValuesNotAffected(): void
    {
        $_ENV['VAR'] = 'resolved';

        try {
            $yaml = <<<'YAML'
                regular: 'some value'
                with_percent: '50%'
                partial_env: 'prefix_%env(VAR)%_suffix'
                env_like: 'env(NOT_REALLY)'
                YAML;

            $result = $this->yamlParser->parse($yaml);

            self::assertSame([
                'regular' => 'some value',
                'with_percent' => '50%',
                'partial_env' => 'prefix_resolved_suffix',
                'env_like' => 'env(NOT_REALLY)',
            ], $result);
        } finally {
            unset($_ENV['VAR']);
        }
    }

    public static function getDataForTestEnvVarNames(): iterable
    {
        yield 'uppercase' => ['UPPER_CASE_VAR', 'upper_value'];
        yield 'with numbers' => ['VAR_123', 'numeric_value'];
        yield 'simple' => ['SIMPLE', 'simple_value'];
    }

    #[DataProvider('getDataForTestEnvVarNames')]
    public function testVariousEnvVarNames(string $varName, string $varValue): void
    {
        $_ENV[$varName] = $varValue;

        try {
            $yaml = "key: '%env({$varName})%'";

            $result = $this->yamlParser->parse($yaml);

            self::assertSame(['key' => $varValue], $result);
        } finally {
            unset($_ENV[$varName]);
        }
    }
}
