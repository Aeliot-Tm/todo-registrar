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
use Aeliot\TodoRegistrar\Service\ColorGenerator;
use Aeliot\TodoRegistrar\Service\ContextPath\ContextPathBuilderRegistry;
use Aeliot\TodoRegistrar\Service\Registrar\GitHub\GitHubRegistrarFactory;
use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
use Aeliot\TodoRegistrarContracts\RegistrarInterface;
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

    public function testCreateWithValidData(): void
    {
        $factory = new GitHubRegistrarFactory(new ColorGenerator(), $this->createIssueSupporter());
        $config = [
            'issue' => [
                'addTagToLabels' => true,
                'labels' => ['bug'],
                'assignees' => ['user1'],
            ],
            'service' => [
                'personalAccessToken' => 'test-token',
                'owner' => 'test-owner',
                'repository' => 'test-repo',
            ],
        ];

        $registrar = $factory->create($config, self::$validator);

        self::assertInstanceOf(RegistrarInterface::class, $registrar);
    }

    public function testCreateThrowsOnInvalidData(): void
    {
        $factory = new GitHubRegistrarFactory(new ColorGenerator(), $this->createIssueSupporter());
        $config = [
            'issue' => [
                'labels' => [123], // Invalid: must be strings
            ],
            'service' => [
                'personalAccessToken' => 'test-token',
                'owner' => 'test-owner',
                'repository' => 'test-repo',
            ],
        ];

        $this->expectException(ConfigValidationException::class);
        $this->expectExceptionMessage('[GitHub] Invalid general issue config');

        $factory->create($config, self::$validator);
    }

    public function testCreateThrowsOnUnknownOptions(): void
    {
        $factory = new GitHubRegistrarFactory(new ColorGenerator(), $this->createIssueSupporter());
        $config = [
            'issue' => [
                'unknown_option' => 'value',
            ],
            'service' => [
                'personalAccessToken' => 'test-token',
                'owner' => 'test-owner',
                'repository' => 'test-repo',
            ],
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->create($config, self::$validator);
    }

    private function createIssueSupporter(): IssueSupporter
    {
        return new IssueSupporter($this->createMock(ContextPathBuilderRegistry::class));
    }
}
