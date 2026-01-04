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
use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
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
        $factory = new GitHubRegistrarFactory(new IssueSupporter());
        $config = [
            'issue' => [
                'addTagToLabels' => true,
                'labels' => ['bug'],
                'assignees' => ['user1'],
            ],
            'service' => [
                'owner' => 'test-owner',
                'repository' => 'test-repo',
            ],
        ];

        $generalConfig = $factory->createGeneralConfig($config, self::$validator);

        self::assertTrue($generalConfig->isAddTagToLabels());
        self::assertSame(['bug'], $generalConfig->getLabels());
        self::assertSame(['user1'], $generalConfig->getAssignees());
        self::assertSame('test-owner', $generalConfig->getOwner());
        self::assertSame('test-repo', $generalConfig->getRepository());
    }

    public function testCreateGeneralConfigThrowsOnInvalidData(): void
    {
        $factory = new GitHubRegistrarFactory(new IssueSupporter());
        $config = [
            'issue' => [
                'labels' => [123], // Invalid: must be strings
            ],
            'service' => [
                'owner' => 'test-owner',
                'repository' => 'test-repo',
            ],
        ];

        $this->expectException(ConfigValidationException::class);
        $this->expectExceptionMessage('[GitHub] Invalid general issue config');

        $factory->createGeneralConfig($config, self::$validator);
    }

    public function testCreateGeneralConfigThrowsOnUnknownOptions(): void
    {
        $factory = new GitHubRegistrarFactory(new IssueSupporter());
        $config = [
            'issue' => [
                'unknown_option' => 'value',
            ],
            'service' => [
                'owner' => 'test-owner',
                'repository' => 'test-repo',
            ],
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralConfig($config, self::$validator);
    }
}
