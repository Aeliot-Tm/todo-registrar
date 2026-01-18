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
use Aeliot\TodoRegistrar\Service\ColorGenerator;
use Aeliot\TodoRegistrar\Service\File\ContextPathBuilder;
use Aeliot\TodoRegistrar\Service\Registrar\GitLab\GitlabRegistrarFactory;
use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
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
        $factory = new GitlabRegistrarFactory(new ColorGenerator(), $this->createIssueSupporter());
        $config = [
            'issue' => [
                'project' => 123,
                'addTagToLabels' => true,
                'labels' => ['bug'],
                'assignee' => ['user1'],
                'milestone' => 'Sprint 1',
                'due_date' => '2025-12-31',
            ],
        ];

        $generalConfig = $factory->createGeneralIssueConfig($config, self::$validator);

        self::assertSame(123, $generalConfig->getProject());
        self::assertTrue($generalConfig->isAddTagToLabels());
        self::assertSame(['bug'], $generalConfig->getLabels());
        self::assertSame(['user1'], $generalConfig->getAssignee());
        self::assertSame('Sprint 1', $generalConfig->getMilestone());
        self::assertSame('2025-12-31', $generalConfig->getDueDate());
    }

    public function testCreateGeneralIssueConfigThrowsOnInvalidData(): void
    {
        $factory = new GitlabRegistrarFactory(new ColorGenerator(), $this->createIssueSupporter());
        $config = [
            'issue' => [
                'project' => 123,
                'labels' => [123], // Invalid: must be strings
            ],
        ];

        $this->expectException(ConfigValidationException::class);
        $this->expectExceptionMessage('[GitLab] Invalid general issue config');

        $factory->createGeneralIssueConfig($config, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnInvalidDueDate(): void
    {
        $factory = new GitlabRegistrarFactory(new ColorGenerator(), $this->createIssueSupporter());
        $config = [
            'issue' => [
                'project' => 123,
                'due_date' => 'invalid-date',
            ],
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($config, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnUnknownOptions(): void
    {
        $factory = new GitlabRegistrarFactory(new ColorGenerator(), $this->createIssueSupporter());
        $config = [
            'issue' => [
                'project' => 123,
                'unknown_option' => 'value',
            ],
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($config, self::$validator);
    }

    private function createIssueSupporter(): IssueSupporter
    {
        return new IssueSupporter($this->createMock(ContextPathBuilder::class));
    }
}
