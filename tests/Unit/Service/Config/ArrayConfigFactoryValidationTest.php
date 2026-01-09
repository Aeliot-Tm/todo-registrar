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

use Aeliot\TodoRegistrar\Dto\GeneralConfig\ArrayConfig;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\Config\ArrayConfigFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(ArrayConfigFactory::class)]
#[CoversClass(ArrayConfig::class)]
final class ArrayConfigFactoryValidationTest extends TestCase
{
    private static ValidatorInterface $validator;

    public static function setUpBeforeClass(): void
    {
        self::$validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testCreateWithValidConfig(): void
    {
        $factory = new ArrayConfigFactory(self::$validator);
        $options = [
            'paths' => ['in' => __DIR__],
            'registrar' => ['type' => 'github', 'options' => ['service' => ['token' => 'xxx']]],
        ];

        $config = $factory->create($options);

        self::assertSame('github', $config->getRegistrarType());
    }

    public function testCreateThrowsOnMissingRegistrar(): void
    {
        $factory = new ArrayConfigFactory(self::$validator);
        $options = [
            'paths' => ['in' => '/path'],
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->create($options);
    }

    public function testCreateThrowsOnInvalidRegistrarType(): void
    {
        $factory = new ArrayConfigFactory(self::$validator);
        $options = [
            'paths' => ['in' => '/path'],
            'registrar' => ['type' => 123],
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->create($options);
    }

    public function testCreateThrowsOnUnknownOptions(): void
    {
        $factory = new ArrayConfigFactory(self::$validator);
        $options = [
            'paths' => ['in' => '/path'],
            'registrar' => ['type' => 'github'],
            'unknown_option' => 'value',
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->create($options);
    }

    public function testExceptionContainsAllViolations(): void
    {
        $factory = new ArrayConfigFactory(self::$validator);
        $options = []; // Missing all required fields

        try {
            $factory->create($options);
            self::fail('Expected ConfigValidationException was not thrown');
        } catch (ConfigValidationException $e) {
            $messages = $e->getErrorMessages();
            self::assertNotEmpty($messages);
            // Should contain errors for both paths and registrar
            $hasRegistrarError = false;
            foreach ($messages as $message) {
                if (str_contains($message, 'registrar')) {
                    $hasRegistrarError = true;
                }
            }
            self::assertTrue($hasRegistrarError, 'Expected error about registrar');
        }
    }

    public function testCreateWithTags(): void
    {
        $factory = new ArrayConfigFactory(self::$validator);
        $options = [
            'paths' => ['in' => __DIR__],
            'registrar' => ['type' => 'github', 'options' => []],
            'tags' => ['todo', 'fixme', 'hack'],
        ];

        $config = $factory->create($options);

        self::assertSame(['todo', 'fixme', 'hack'], $config->getTags());
    }

    public function testCreateWithoutTagsUsesDefault(): void
    {
        $factory = new ArrayConfigFactory(self::$validator);
        $options = [
            'paths' => ['in' => __DIR__],
            'registrar' => ['type' => 'github', 'options' => []],
        ];

        $config = $factory->create($options);

        // Default tags are set in Config class
        self::assertSame(['todo', 'fixme'], $config->getTags());
    }
}
