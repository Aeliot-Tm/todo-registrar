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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\File\ContextPathBuilder;
use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
use Aeliot\TodoRegistrar\Service\Registrar\JIRA\JiraRegistrarFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(JiraRegistrarFactory::class)]
final class JiraRegistrarFactoryTest extends TestCase
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
        $factory = new JiraRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'addTagToLabels' => true,
            'labels' => ['bug'],
            'components' => ['Backend'],
            'assignee' => 'developer1',
            'priority' => 'High',
        ];

        $config = $factory->createGeneralIssueConfig($issueConfig, self::$validator);

        self::assertSame('PROJ', $config->getProjectKey());
        self::assertSame('Task', $config->getIssueType());
        self::assertTrue($config->isAddTagToLabels());
        self::assertSame(['bug'], $config->getLabels());
        self::assertSame(['Backend'], $config->getComponents());
        self::assertSame('developer1', $config->getAssignee());
        self::assertSame('High', $config->getPriority());
    }

    public function testCreateGeneralIssueConfigThrowsOnMissingProjectKey(): void
    {
        $factory = new JiraRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'issueType' => 'Task',
        ];

        $this->expectException(ConfigValidationException::class);
        $this->expectExceptionMessage('[JIRA] Invalid general issue config');

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnMissingIssueType(): void
    {
        $factory = new JiraRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'projectKey' => 'PROJ',
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnInvalidLabels(): void
    {
        $factory = new JiraRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'labels' => [123], // Invalid: must be strings
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnConflictingTypeKeys(): void
    {
        $factory = new JiraRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'type' => 'Bug', // Conflicting with issueType
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnOutdatedTypeProperty(): void
    {
        $factory = new JiraRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'projectKey' => 'PROJ',
            'type' => 'Task', // Outdated property
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    public function testCreateGeneralIssueConfigThrowsOnUnknownOptions(): void
    {
        $factory = new JiraRegistrarFactory($this->createIssueSupporter());
        $issueConfig = [
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'unknown_option' => 'value',
        ];

        $this->expectException(ConfigValidationException::class);

        $factory->createGeneralIssueConfig($issueConfig, self::$validator);
    }

    private function createIssueSupporter(): IssueSupporter
    {
        return new IssueSupporter($this->createMock(ContextPathBuilder::class));
    }
}
