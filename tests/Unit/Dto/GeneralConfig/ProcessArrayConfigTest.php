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

use Aeliot\TodoRegistrar\Dto\GeneralConfig\ProcessArrayConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(ProcessArrayConfig::class)]
final class ProcessArrayConfigTest extends TestCase
{
    private static ValidatorInterface $validator;

    public static function setUpBeforeClass(): void
    {
        self::$validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidConfig(): void
    {
        $config = new ProcessArrayConfig(['glueSequentialComments' => true]);
        self::assertTrue($config->isGlueSequentialComments());

        $violations = self::$validator->validate($config);
        self::assertCount(0, $violations);
    }

    public function testDefaultValue(): void
    {
        $config = new ProcessArrayConfig([]);
        self::assertFalse($config->isGlueSequentialComments());
    }

    #[DataProvider('getInvalidTypeData')]
    public function testInvalidType(mixed $value, string $expectedMessage): void
    {
        $config = new ProcessArrayConfig(['glueSequentialComments' => $value]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        $messages = array_map(static fn ($v) => $v->getMessage(), iterator_to_array($violations));
        self::assertContains($expectedMessage, $messages);
    }

    public static function getInvalidTypeData(): iterable
    {
        yield 'string' => ['true', 'Option "process.glueSequentialComments" must be a boolean'];
        yield 'int' => [1, 'Option "process.glueSequentialComments" must be a boolean'];
        yield 'array' => [[], 'Option "process.glueSequentialComments" must be a boolean'];
    }

    public function testUnknownKeys(): void
    {
        $config = new ProcessArrayConfig(['glueSequentialComments' => true, 'unknownKey' => 'value']);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        $messages = array_map(static fn ($v) => $v->getMessage(), iterator_to_array($violations));
        $found = false;
        foreach ($messages as $message) {
            if (str_contains($message, 'Unknown "process" options detected')) {
                $found = true;
                break;
            }
        }
        self::assertTrue($found, 'Expected unknown keys error message');
    }
}
