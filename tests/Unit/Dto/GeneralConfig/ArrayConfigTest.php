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

namespace Aeliot\TodoRegistrar\Test\Unit\Dto\GeneralConfig;

use Aeliot\TodoRegistrar\Dto\GeneralConfig\ArrayConfig;
use Aeliot\TodoRegistrar\Dto\GeneralConfig\PathsConfig;
use Aeliot\TodoRegistrar\Dto\GeneralConfig\RegistrarConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(ArrayConfig::class)]
#[CoversClass(PathsConfig::class)]
#[CoversClass(RegistrarConfig::class)]
final class ArrayConfigTest extends TestCase
{
    private static ValidatorInterface $validator;

    public static function setUpBeforeClass(): void
    {
        self::$validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public static function getDataForTestValidConfig(): iterable
    {
        yield 'minimal config - only registrar' => [
            [
                'registrar' => ['type' => 'github'],
            ],
        ];

        yield 'config without paths' => [
            [
                'registrar' => ['type' => 'gitlab'],
                'tags' => ['todo'],
            ],
        ];

        yield 'config with paths' => [
            [
                'paths' => ['in' => '/path/to/src'],
                'registrar' => ['type' => 'github'],
            ],
        ];

        yield 'config with empty paths' => [
            [
                'paths' => [],
                'registrar' => ['type' => 'github'],
            ],
        ];

        yield 'full config' => [
            [
                'paths' => [
                    'in' => ['/path/to/src', '/path/to/lib'],
                    'append' => '/path/to/extra',
                    'exclude' => ['vendor', 'node_modules'],
                ],
                'registrar' => [
                    'type' => 'jira',
                    'options' => ['projectKey' => 'PROJ'],
                ],
                'tags' => ['todo', 'fixme', 'hack'],
            ],
        ];

        yield 'paths.in as string' => [
            [
                'paths' => ['in' => '/single/path'],
                'registrar' => ['type' => 'gitlab'],
            ],
        ];

        yield 'paths.in as array' => [
            [
                'paths' => ['in' => ['/path/one', '/path/two']],
                'registrar' => ['type' => 'github'],
            ],
        ];

        yield 'paths.append as string' => [
            [
                'paths' => ['append' => '/extra/path'],
                'registrar' => ['type' => 'github'],
            ],
        ];

        yield 'paths.append as array' => [
            [
                'paths' => ['append' => ['/extra/one', '/extra/two']],
                'registrar' => ['type' => 'github'],
            ],
        ];

        yield 'paths.exclude as string' => [
            [
                'paths' => ['exclude' => 'vendor'],
                'registrar' => ['type' => 'github'],
            ],
        ];

        yield 'paths.exclude as array' => [
            [
                'paths' => ['exclude' => ['vendor', 'node_modules']],
                'registrar' => ['type' => 'github'],
            ],
        ];

        yield 'empty tags array' => [
            [
                'registrar' => ['type' => 'github'],
                'tags' => [],
            ],
        ];

        yield 'config with issueKeyInjection - all fields' => [
            [
                'registrar' => ['type' => 'github'],
                'issueKeyInjection' => [
                    'position' => 'after_separator',
                    'newSeparator' => '|',
                    'replaceSeparator' => true,
                    'summarySeparators' => [':', '-'],
                ],
            ],
        ];

        yield 'config with issueKeyInjection - minimal' => [
            [
                'registrar' => ['type' => 'github'],
                'issueKeyInjection' => [
                    'position' => 'before_separator',
                ],
            ],
        ];

        yield 'config without issueKeyInjection' => [
            [
                'registrar' => ['type' => 'github'],
            ],
        ];
    }

    public static function getDataForTestMissingRequiredFields(): iterable
    {
        yield 'missing registrar' => [
            ['paths' => []],
            'Option "registrar" is required',
        ];

        yield 'missing registrar.type' => [
            ['registrar' => []],
            'Option "registrar.type" is required',
        ];

        yield 'empty registrar.type' => [
            ['paths' => ['in' => '/path'], 'registrar' => ['type' => '']],
            'Option "registrar.type" is required',
        ];
    }

    public static function getDataForTestInvalidTypes(): iterable
    {
        yield 'paths is string' => [
            ['paths' => 'invalid', 'registrar' => ['type' => 'github']],
            'Option "paths" must be an array',
        ];

        yield 'paths is int' => [
            ['paths' => 123, 'registrar' => ['type' => 'github']],
            'Option "paths" must be an array',
        ];

        yield 'paths.in is int' => [
            ['paths' => ['in' => 123], 'registrar' => ['type' => 'github']],
            'Option "paths.in" must be a string or array of strings',
        ];

        yield 'paths.in array contains int' => [
            ['paths' => ['in' => ['/path', 123]], 'registrar' => ['type' => 'github']],
            'Each path in "paths.in" must be a string',
        ];

        yield 'paths.append is int' => [
            ['paths' => ['append' => 123], 'registrar' => ['type' => 'github']],
            'Option "paths.append" must be a string or array of strings',
        ];

        yield 'paths.append array contains int' => [
            ['paths' => ['append' => ['/path', 123]], 'registrar' => ['type' => 'github']],
            'Each path in "paths.append" must be a string',
        ];

        yield 'paths.exclude is int' => [
            ['paths' => ['exclude' => 123], 'registrar' => ['type' => 'github']],
            'Option "paths.exclude" must be a string or array of strings',
        ];

        yield 'paths.exclude array contains int' => [
            ['paths' => ['exclude' => ['vendor', 123]], 'registrar' => ['type' => 'github']],
            'Each path in "paths.exclude" must be a string',
        ];

        yield 'registrar is string' => [
            ['registrar' => 'github'],
            'Option "registrar" must be an array',
        ];

        yield 'registrar is int' => [
            ['registrar' => 123],
            'Option "registrar" must be an array',
        ];

        yield 'registrar.type is int' => [
            ['registrar' => ['type' => 123]],
            'Option "registrar.type" must be a string',
        ];

        yield 'registrar.options is string' => [
            ['registrar' => ['type' => 'github', 'options' => 'invalid']],
            'Option "registrar.options" must be an array',
        ];

        yield 'registrar.options is int' => [
            ['registrar' => ['type' => 'github', 'options' => 123]],
            'Option "registrar.options" must be an array',
        ];

        yield 'tags is string' => [
            ['registrar' => ['type' => 'github'], 'tags' => 'todo'],
            'Option "tags" must be an array of strings',
        ];

        yield 'tags contains int' => [
            ['registrar' => ['type' => 'github'], 'tags' => ['todo', 123]],
            'Each tag must be a string',
        ];

        yield 'issueKeyInjection is string' => [
            ['registrar' => ['type' => 'github'], 'issueKeyInjection' => 'invalid'],
            'Option "issueKeyInjection" must be an array',
        ];

        yield 'issueKeyInjection is int' => [
            ['registrar' => ['type' => 'github'], 'issueKeyInjection' => 123],
            'Option "issueKeyInjection" must be an array',
        ];
    }

    #[DataProvider('getDataForTestValidConfig')]
    public function testValidConfig(array $options): void
    {
        $config = new ArrayConfig($options);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    #[DataProvider('getDataForTestMissingRequiredFields')]
    public function testMissingRequiredFields(array $options, string $expectedMessage): void
    {
        $config = new ArrayConfig($options);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage($expectedMessage, $violations);
    }

    #[DataProvider('getDataForTestInvalidTypes')]
    public function testInvalidTypes(array $options, string $expectedMessage): void
    {
        $config = new ArrayConfig($options);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessagePart($expectedMessage, $violations);
    }

    public function testUnknownOptionsDetected(): void
    {
        $options = [
            'paths' => ['in' => '/path'],
            'registrar' => ['type' => 'github'],
            'unknown_option' => 'value',
        ];
        $config = new ArrayConfig($options);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        $hasError = false;
        foreach ($violations as $violation) {
            if (str_contains((string) $violation->getMessage(), 'Unknown configuration options detected')) {
                $hasError = true;
                break;
            }
        }
        self::assertTrue($hasError, 'Expected validation error for unknown configuration options');
    }

    public function testUnknownPathsOptionsDetected(): void
    {
        $options = [
            'paths' => ['in' => '/path', 'unknown_path_option' => 'value'],
            'registrar' => ['type' => 'github'],
        ];
        $config = new ArrayConfig($options);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessagePart('Unknown "paths" options detected', $violations);
    }

    public function testUnknownRegistrarOptionsDetected(): void
    {
        $options = [
            'paths' => ['in' => '/path'],
            'registrar' => ['type' => 'github', 'unknown_registrar_option' => 'value'],
        ];
        $config = new ArrayConfig($options);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessagePart('Unknown "registrar" options detected', $violations);
    }

    public function testGetters(): void
    {
        $options = [
            'paths' => [
                'in' => ['/path/to/src'],
                'append' => '/extra',
                'exclude' => ['vendor'],
            ],
            'registrar' => [
                'type' => 'jira',
                'options' => ['projectKey' => 'PROJ'],
            ],
            'tags' => ['todo', 'fixme'],
        ];
        $config = new ArrayConfig($options);

        $pathsConfig = $config->getPaths();
        self::assertNotNull($pathsConfig);
        self::assertSame(['/path/to/src'], $pathsConfig->getIn());
        self::assertSame('/extra', $pathsConfig->getAppend());
        self::assertSame(['vendor'], $pathsConfig->getExclude());

        self::assertSame('jira', $config->getRegistrar()->getType());
        self::assertSame(['projectKey' => 'PROJ'], $config->getRegistrar()->getOptions());

        self::assertSame(['todo', 'fixme'], $config->getTags());
    }

    public function testOptionalFieldsCanBeNull(): void
    {
        $options = [
            'paths' => ['in' => '/path'],
            'registrar' => ['type' => 'github'],
        ];
        $config = new ArrayConfig($options);

        self::assertNull($config->getPaths()->getAppend());
        self::assertNull($config->getPaths()->getExclude());
        self::assertNull($config->getRegistrar()->getOptions());

        $violations = self::$validator->validate($config);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testPathsCanBeOmitted(): void
    {
        $options = [
            'registrar' => ['type' => 'github'],
        ];
        $config = new ArrayConfig($options);

        self::assertNull($config->getPaths());

        $violations = self::$validator->validate($config);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testTagsDefaultsToEmptyArray(): void
    {
        $options = [
            'registrar' => ['type' => 'github'],
        ];
        $config = new ArrayConfig($options);

        self::assertSame([], $config->getTags());

        $violations = self::$validator->validate($config);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testInvalidPathsTypeHasViolation(): void
    {
        $options = [
            'paths' => 'invalid',
            'registrar' => ['type' => 'github'],
        ];
        $config = new ArrayConfig($options);

        $violations = self::$validator->validate($config);
        self::assertCount(1, $violations, $this->formatViolations($violations));
        self::assertContainsMessagePart('Option "paths" must be an array', $violations);
    }

    public function testInvalidRegistrarTypeHasViolation(): void
    {
        $options = [
            'registrar' => 'invalid',
        ];
        $config = new ArrayConfig($options);

        $violations = self::$validator->validate($config);
        self::assertCount(1, $violations, $this->formatViolations($violations));
        self::assertContainsMessagePart('Option "registrar" must be an array', $violations);
    }

    public function testPathsInCanBeOmitted(): void
    {
        $options = [
            'paths' => ['append' => '/extra'],
            'registrar' => ['type' => 'github'],
        ];
        $config = new ArrayConfig($options);

        self::assertNull($config->getPaths()->getIn());
        self::assertSame('/extra', $config->getPaths()->getAppend());

        $violations = self::$validator->validate($config);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testOldRootLevelIssueKeyFieldsAreRejected(): void
    {
        $options = [
            'registrar' => ['type' => 'github'],
            'issueKeyPosition' => 'after_separator',
        ];
        $config = new ArrayConfig($options);

        $violations = self::$validator->validate($config);
        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessagePart('Unknown configuration options detected', $violations);
    }

    public function testIssueKeyInjectionGetterReturnsConfig(): void
    {
        $options = [
            'registrar' => ['type' => 'github'],
            'issueKeyInjection' => [
                'position' => 'before_separator',
                'newSeparator' => '|',
                'replaceSeparator' => true,
                'summarySeparators' => [':', '-', '='],
            ],
        ];
        $config = new ArrayConfig($options);

        $injectionConfig = $config->getIssueKeyInjection();
        self::assertNotNull($injectionConfig);
        self::assertSame('before_separator', $injectionConfig->getPosition());
        self::assertSame('|', $injectionConfig->getNewSeparator());
        self::assertTrue($injectionConfig->getReplaceSeparator());
        self::assertSame([':', '-', '='], $injectionConfig->getSummarySeparators());

        $violations = self::$validator->validate($config);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testIssueKeyInjectionCanBeOmitted(): void
    {
        $options = [
            'registrar' => ['type' => 'github'],
        ];
        $config = new ArrayConfig($options);

        self::assertNull($config->getIssueKeyInjection());

        $violations = self::$validator->validate($config);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testIssueKeyInjectionWithNewPositionField(): void
    {
        $options = [
            'registrar' => ['type' => 'github'],
            'issueKeyInjection' => [
                'position' => 'before_separator',
                'newSeparator' => '|',
                'replaceSeparator' => true,
                'summarySeparators' => [':', '-'],
            ],
        ];
        $config = new ArrayConfig($options);

        $injectionConfig = $config->getIssueKeyInjection();
        self::assertNotNull($injectionConfig);
        self::assertSame('before_separator', $injectionConfig->getPosition());

        $violations = self::$validator->validate($config);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testIssueKeyInjectionWithOldIssueKeyPositionField(): void
    {
        $options = [
            'registrar' => ['type' => 'github'],
            'issueKeyInjection' => [
                'position' => 'after_separator',
                'newSeparator' => '=',
                'replaceSeparator' => false,
                'summarySeparators' => [':'],
            ],
        ];
        $config = new ArrayConfig($options);

        $injectionConfig = $config->getIssueKeyInjection();
        self::assertNotNull($injectionConfig);
        self::assertSame('after_separator', $injectionConfig->getPosition());

        $violations = self::$validator->validate($config);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testIssueKeyInjectionInvalidPositionValue(): void
    {
        $options = [
            'registrar' => ['type' => 'github'],
            'issueKeyInjection' => [
                'position' => 'invalid_value',
            ],
        ];
        $config = new ArrayConfig($options);

        $violations = self::$validator->validate($config);
        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessagePart('Option "issueKeyInjection.position" must be one of:', $violations);
    }

    public function testIssueKeyInjectionInvalidIssueKeyPositionValue(): void
    {
        $options = [
            'registrar' => ['type' => 'github'],
            'issueKeyInjection' => [
                'position' => 'invalid_value',
            ],
        ];
        $config = new ArrayConfig($options);

        $violations = self::$validator->validate($config);
        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessagePart('Option "issueKeyInjection.position" must be one of:', $violations);
    }

    private static function assertContainsMessage(string $expected, iterable $violations): void
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = (string) $violation->getMessage();
        }
        self::assertContains($expected, $messages, \sprintf(
            'Expected message "%s" not found in violations: %s',
            $expected,
            implode(', ', $messages)
        ));
    }

    private static function assertContainsMessagePart(string $expectedPart, iterable $violations): void
    {
        $messages = [];
        foreach ($violations as $violation) {
            $message = (string) $violation->getMessage();
            $messages[] = $message;
            if (str_contains($message, $expectedPart)) {
                return;
            }
        }
        self::fail(\sprintf(
            'Expected message part "%s" not found in violations: %s',
            $expectedPart,
            implode(', ', $messages)
        ));
    }

    private function formatViolations(iterable $violations): string
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }

        return implode("\n", $messages);
    }
}
