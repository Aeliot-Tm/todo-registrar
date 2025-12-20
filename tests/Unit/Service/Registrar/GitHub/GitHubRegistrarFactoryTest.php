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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar\GitHub;

use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\Registrar\GitHub\GitHubRegistrarFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(GitHubRegistrarFactory::class)]
final class GitHubRegistrarFactoryTest extends TestCase
{
    private static ValidatorInterface $validator;

    public static function setUpBeforeClass(): void
    {
        self::$validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testCreateGeneralConfigWithValidData(): void
    {
        $factory = new GitHubRegistrarFactory();
        $issueConfig = [
            'addTagToLabels' => true,
            'labels' => ['bug'],
            'assignees' => ['user1'],
        ];

        $config = $factory->createGeneralConfig($issueConfig, self::$validator);

        self::assertTrue($config->isAddTagToLabels());
        self::assertSame(['bug'], $config->getLabels());
        self::assertSame(['user1'], $config->getAssignees());
    }

    public function testCreateGeneralConfigThrowsOnInvalidData(): void
    {
        $factory = new GitHubRegistrarFactory();
        $issueConfig = [
            'labels' => [123], // Invalid: must be strings
        ];

        $this->expectException(ConfigValidationException::class);
        $this->expectExceptionMessage('[GitHub] Invalid general issue config');

        $factory->createGeneralConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralConfigThrowsOnUnknownOptions(): void
    {
        $factory = new GitHubRegistrarFactory();
        $issueConfig = [
            'unknown_option' => 'value',
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralConfig($issueConfig, self::$validator);
    }
}
