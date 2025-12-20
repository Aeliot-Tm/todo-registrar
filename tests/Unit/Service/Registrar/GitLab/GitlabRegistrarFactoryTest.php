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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar\GitLab;

use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\Registrar\GitLab\GeneralIssueConfig;
use Aeliot\TodoRegistrar\Service\Registrar\GitLab\GitlabRegistrarFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(GitlabRegistrarFactory::class)]
final class GitlabRegistrarFactoryTest extends TestCase
{
    private static ValidatorInterface $validator;

    public static function setUpBeforeClass(): void
    {
        self::$validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testCreateGeneralIssueConfigWithValidData(): void
    {
        $factory = new GitlabRegistrarFactory();
        $issueConfig = [
            'addTagToLabels' => true,
            'labels' => ['bug'],
            'assignee' => ['user1'],
            'milestone' => 'Sprint 1',
            'due_date' => '2025-12-31',
        ];

        $config = $factory->createGeneralIssueConfig($issueConfig, self::$validator);

        self::assertInstanceOf(GeneralIssueConfig::class, $config);
        self::assertTrue($config->isAddTagToLabels());
        self::assertSame(['bug'], $config->getLabels());
        self::assertSame(['user1'], $config->getAssignee());
        self::assertSame('Sprint 1', $config->getMilestone());
        self::assertSame('2025-12-31', $config->getDueDate());
    }

    public function testCreateGeneralIssueConfigThrowsOnInvalidData(): void
    {
        $factory = new GitlabRegistrarFactory();
        $issueConfig = [
            'labels' => [123], // Invalid: must be strings
        ];

        $this->expectException(ConfigValidationException::class);
        $this->expectExceptionMessage('[GitLab] Invalid general issue config');

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnInvalidDueDate(): void
    {
        $factory = new GitlabRegistrarFactory();
        $issueConfig = [
            'due_date' => 'invalid-date',
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnUnknownOptions(): void
    {
        $factory = new GitlabRegistrarFactory();
        $issueConfig = [
            'unknown_option' => 'value',
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }
}
