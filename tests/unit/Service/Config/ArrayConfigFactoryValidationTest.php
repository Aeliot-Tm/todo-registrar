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
use Aeliot\TodoRegistrar\Dto\GeneralConfig\PathsConfig;
use Aeliot\TodoRegistrar\Dto\GeneralConfig\ProcessConfig;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\Config\ArrayConfigFactory;
use Aeliot\TodoRegistrar\Service\File\Finder;
use Aeliot\TodoRegistrar\Service\File\FinderNamePatternBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder as SymfonyFinder;
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

    public function testCreateAppliesDefaultExtensionsPattern(): void
    {
        $config = $this->createFactory()->create([
            'paths' => ['in' => __DIR__],
            'registrar' => ['type' => 'github', 'options' => []],
        ]);

        $expected = (new FinderNamePatternBuilder())->buildFromExtensions(PathsConfig::DEFAULT_EXTENSIONS);
        self::assertContains($expected, $this->getFinderNames($config->getFinder()));
    }

    public function testCreateWithValidConfig(): void
    {
        $factory = $this->createFactory();
        $options = [
            'paths' => ['in' => __DIR__],
            'registrar' => ['type' => 'github', 'options' => ['service' => ['token' => 'xxx']]],
        ];

        $config = $factory->create($options);

        self::assertSame('github', $config->getRegistrarType());
    }

    public function testCreateThrowsOnMissingRegistrar(): void
    {
        $factory = $this->createFactory();
        $options = [
            'paths' => ['in' => '/path'],
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->create($options);
    }

    public function testCreateThrowsOnInvalidRegistrarType(): void
    {
        $factory = $this->createFactory();
        $options = [
            'paths' => ['in' => '/path'],
            'registrar' => ['type' => 123],
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->create($options);
    }

    public function testCreateThrowsOnUnknownOptions(): void
    {
        $factory = $this->createFactory();
        $options = [
            'paths' => ['in' => '/path'],
            'registrar' => ['type' => 'github'],
            'unknown_option' => 'value',
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->create($options);
    }

    public function testCreateWithExtensionAliases(): void
    {
        $config = $this->createFactory()->create([
            'paths' => ['in' => __DIR__],
            'registrar' => ['type' => 'github', 'options' => []],
            'process' => [
                'extensionAliases' => ['module' => 'php'],
            ],
        ]);

        $processConfig = $config->getProcessConfig();
        self::assertInstanceOf(ProcessConfig::class, $processConfig);
        self::assertSame(['module' => 'php'], $processConfig->getExtensionAliases());
    }

    public function testCreateWithPathsExtensions(): void
    {
        $config = $this->createFactory()->create([
            'paths' => ['in' => __DIR__, 'extensions' => ['php', 'module']],
            'registrar' => ['type' => 'github', 'options' => []],
        ]);

        $expected = (new FinderNamePatternBuilder())->buildFromExtensions(['php', 'module']);
        self::assertContains($expected, $this->getFinderNames($config->getFinder()));
    }

    public function testCreateWithPathsExtensionsAsString(): void
    {
        $config = $this->createFactory()->create([
            'paths' => ['in' => __DIR__, 'extensions' => 'module'],
            'registrar' => ['type' => 'github', 'options' => []],
        ]);

        $expected = (new FinderNamePatternBuilder())->buildFromExtensions(['module']);
        self::assertContains($expected, $this->getFinderNames($config->getFinder()));
    }

    public function testCreateWithPathsName(): void
    {
        $config = $this->createFactory()->create([
            'paths' => ['in' => __DIR__, 'name' => '/\.custom$/'],
            'registrar' => ['type' => 'github', 'options' => []],
        ]);

        self::assertContains('/\.custom$/', $this->getFinderNames($config->getFinder()));
    }

    public function testCreateWithPathsNameAndExtensions(): void
    {
        $config = $this->createFactory()->create([
            'paths' => [
                'in' => __DIR__,
                'name' => '/\.custom$/',
                'extensions' => ['module'],
            ],
            'registrar' => ['type' => 'github', 'options' => []],
        ]);

        $names = $this->getFinderNames($config->getFinder());
        self::assertContains('/\.custom$/', $names);
        self::assertContains(
            (new FinderNamePatternBuilder())->buildFromExtensions(['module']),
            $names,
        );
    }

    public function testExceptionContainsAllViolations(): void
    {
        $factory = $this->createFactory();
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
        $factory = $this->createFactory();
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
        $factory = $this->createFactory();
        $options = [
            'paths' => ['in' => __DIR__],
            'registrar' => ['type' => 'github', 'options' => []],
        ];

        $config = $factory->create($options);

        // Default tags are set in Config class
        self::assertSame(['todo', 'fixme'], $config->getTags());
    }

    private function createFactory(): ArrayConfigFactory
    {
        return new ArrayConfigFactory(new FinderNamePatternBuilder(), self::$validator);
    }

    /**
     * @param Finder $finder
     *
     * @return list<string|\Closure>
     */
    private function getFinderNames(SymfonyFinder $finder): array
    {
        $reflection = new \ReflectionClass(SymfonyFinder::class);
        $property = $reflection->getProperty('names');
        $property->setAccessible(true);

        return $property->getValue($finder);
    }
}
