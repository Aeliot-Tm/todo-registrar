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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar\Redmine;

use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\ContextPath\ContextPathBuilderRegistry;
use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\RedmineRegistrarFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(RedmineRegistrarFactory::class)]
final class RedmineRegistrarFactoryTest extends TestCase
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
        $factory = new RedmineRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'project' => 1,
            'tracker' => 'Bug',
            'assignee' => 'developer1',
            'priority' => 'High',
            'category' => 'Backend',
            'fixed_version' => 'v1.0',
            'start_date' => '2024-01-01',
            'due_date' => '2024-12-31',
            'estimated_hours' => 8.5,
        ];

        $config = $factory->createGeneralIssueConfig($issueConfig, self::$validator);

        self::assertSame(1, $config->getProjectIdentifier());
        self::assertSame('Bug', $config->getTracker());
        self::assertSame('developer1', $config->getAssignee());
        self::assertSame('High', $config->getPriority());
        self::assertSame('Backend', $config->getCategory());
        self::assertSame('v1.0', $config->getFixedVersion());
        self::assertSame('2024-01-01', $config->getStartDate());
        self::assertSame('2024-12-31', $config->getDueDate());
        self::assertSame(8.5, $config->getEstimatedHours());
    }

    public function testCreateGeneralIssueConfigThrowsOnMissingProject(): void
    {
        $factory = new RedmineRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'tracker' => 'Bug',
        ];

        $this->expectException(ConfigValidationException::class);
        $this->expectExceptionMessage('[Redmine] Invalid general issue config');

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnMissingTracker(): void
    {
        $factory = new RedmineRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'project' => 1,
        ];

        $this->expectException(ConfigValidationException::class);
        $this->expectExceptionMessage('[Redmine] Invalid general issue config');

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnInvalidDueDate(): void
    {
        $factory = new RedmineRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'project' => 1,
            'tracker' => 'Bug',
            'due_date' => 'invalid-date',
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnInvalidStartDate(): void
    {
        $factory = new RedmineRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'project' => 1,
            'tracker' => 'Bug',
            'start_date' => 'invalid-date',
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigWithValidEstimatedHours(): void
    {
        $factory = new RedmineRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'project' => 1,
            'tracker' => 'Bug',
            'estimated_hours' => 8.5,
        ];

        $config = $factory->createGeneralIssueConfig($issueConfig, self::$validator);

        self::assertSame(8.5, $config->getEstimatedHours());
    }

    public function testCreateGeneralIssueConfigThrowsOnUnknownOptions(): void
    {
        $factory = new RedmineRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'project' => 1,
            'tracker' => 'Bug',
            'unknown_option' => 'value',
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    private function createIssueSupporter(): IssueSupporter
    {
        return new IssueSupporter($this->createMock(ContextPathBuilderRegistry::class));
    }
}
