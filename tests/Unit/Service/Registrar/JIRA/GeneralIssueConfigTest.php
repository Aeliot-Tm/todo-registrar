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

use Aeliot\TodoRegistrar\Service\Registrar\AbstractGeneralIssueConfig;
use Aeliot\TodoRegistrar\Service\Registrar\JIRA\GeneralIssueConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(GeneralIssueConfig::class)]
#[CoversClass(AbstractGeneralIssueConfig::class)]
final class GeneralIssueConfigTest extends TestCase
{
    private static ValidatorInterface $validator;

    public static function setUpBeforeClass(): void
    {
        self::$validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public static function getDataForTestValidConfig(): iterable
    {
        yield 'minimal config' => [
            [
                'projectKey' => 'PROJ',
                'issueType' => 'Task',
            ],
        ];

        yield 'full config' => [
            [
                'projectKey' => 'PROJ',
                'issueType' => 'Bug',
                'addTagToLabels' => true,
                'labels' => ['tech-debt', 'from-code'],
                'tagPrefix' => 'tag-',
                'summaryPrefix' => '[TODO] ',
                'components' => ['Backend', 'API'],
                'assignee' => 'developer1',
                'priority' => 'High',
            ],
        ];

        // Note: 'type' alias is deprecated - use 'issueType' instead
    }

    public static function getDataForTestRequiredFields(): iterable
    {
        yield 'missing projectKey' => [
            ['issueType' => 'Task'],
            'Option "projectKey" is required for JIRA registrar',
        ];

        yield 'missing issueType' => [
            ['projectKey' => 'PROJ'],
            'Option "issueType" is required for JIRA registrar',
        ];
    }

    public static function getDataForTestInvalidComponentsType(): iterable
    {
        // Note: 'components is string' - normalization casts to array, so no error
        yield 'components contains int' => [
            ['projectKey' => 'PROJ', 'issueType' => 'Task', 'components' => [123]],
            'Each component must be a string JIRA component name',
        ];
    }

    #[DataProvider('getDataForTestValidConfig')]
    public function testValidConfig(array $config): void
    {
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    #[DataProvider('getDataForTestRequiredFields')]
    public function testRequiredFields(array $config, string $expectedMessage): void
    {
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage($expectedMessage, $violations);
    }

    #[DataProvider('getDataForTestInvalidComponentsType')]
    public function testInvalidComponentsType(array $config, string $expectedMessage): void
    {
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage($expectedMessage, $violations);
    }

    public function testIssueTypeAlias(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'type' => 'Bug',
        ]);

        self::assertSame('Bug', $config->getIssueType());
    }

    public function testConflictingTypeKeys(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'type' => 'Task',
            'issueType' => 'Bug',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage(
            'Conflicting config: both "issueType" and "type" are specified. Use only one of them',
            $violations
        );
    }

    public function testOutdatedTypeProperty(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'type' => 'Task',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage(
            'Used outdated property "type", but "issueType" must be used',
            $violations
        );
    }

    public function testUnknownOptionsDetected(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'unknown_option' => 'value',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        $hasError = false;
        foreach ($violations as $violation) {
            if (str_contains((string) $violation->getMessage(), 'Unknown configuration options detected')) {
                $hasError = true;
                break;
            }
        }
        self::assertTrue($hasError, 'Expected validation error for unknown configuration options');
    }

    public function testOptionalAssigneeCanBeNull(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'assignee' => null,
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testOptionalPriorityCanBeNull(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'priority' => null,
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testInvalidAssigneeType(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'assignee' => ['array-not-allowed'],
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
    }

    public function testInvalidPriorityType(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'priority' => 123,
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
    }

    public function testValueAssigning(): void
    {
        $config = new GeneralIssueConfig([
            'addTagToLabels' => true,
            'components' => ['Component-1', 'Component-2'],
            'labels' => ['Label-1', 'Label-2'],
            'priority' => 'Low',
            'summaryPrefix' => 'a-summary-prefix. ',
            'tagPrefix' => 'tag-',
            'issueType' => 'Bug',
            'projectKey' => 'Todo',
            'assignee' => 'developer1',
        ]);

        self::assertTrue($config->isAddTagToLabels());
        self::assertSame('Bug', $config->getIssueType());
        self::assertSame('Low', $config->getPriority());
        self::assertSame('a-summary-prefix. ', $config->getSummaryPrefix());
        self::assertSame('tag-', $config->getTagPrefix());
        self::assertSame(['Component-1', 'Component-2'], $config->getComponents());
        self::assertSame(['Label-1', 'Label-2'], $config->getLabels());
        self::assertSame('Todo', $config->getProjectKey());
        self::assertSame('developer1', $config->getAssignee());
    }

    public function testDefaultValues(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
        ]);

        self::assertFalse($config->isAddTagToLabels());
        self::assertSame([], $config->getLabels());
        self::assertSame('', $config->getTagPrefix());
        self::assertSame('', $config->getSummaryPrefix());
        self::assertSame([], $config->getComponents());
        self::assertNull($config->getAssignee());
        self::assertNull($config->getPriority());
    }

    public function testInvalidLabelsElementType(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'labels' => [123, 'valid'],
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Each label must be a string', $violations);
    }

    public function testValidAssigneeAsString(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'assignee' => 'developer1',
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
        self::assertSame('developer1', $config->getAssignee());
    }

    public function testValidPriorityAsString(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'priority' => 'High',
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
        self::assertSame('High', $config->getPriority());
    }

    public function testEmptyProjectKeyIsInvalid(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => '',
            'issueType' => 'Task',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "projectKey" is required for JIRA registrar', $violations);
    }

    public function testEmptyIssueTypeIsInvalid(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => '',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "issueType" is required for JIRA registrar', $violations);
    }

    public function testInvalidProjectKeyType(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 123,
            'issueType' => 'Task',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "projectKey" must be a string (e.g., "PROJ")', $violations);
    }

    public function testInvalidIssueTypeType(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 123,
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "issueType" must be a string (e.g., "Task", "Bug", "Story")', $violations);
    }

    public function testInvalidTagPrefixType(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'tagPrefix' => 123,
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "tagPrefix" must be a string', $violations);
    }

    public function testInvalidSummaryPrefixType(): void
    {
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'summaryPrefix' => 123,
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "summaryPrefix" must be a string', $violations);
    }

    public function testInvalidAddTagToLabelsType(): void
    {
        // This should pass because normalization casts to bool
        $config = new GeneralIssueConfig([
            'projectKey' => 'PROJ',
            'issueType' => 'Task',
            'addTagToLabels' => 'not-a-bool',
        ]);
        $violations = self::$validator->validate($config);

        // String is cast to bool (truthy), so no validation error
        self::assertCount(0, $violations, $this->formatViolations($violations));
        self::assertTrue($config->isAddTagToLabels());
    }

    private static function assertContainsMessage(string $expected, iterable $violations): void
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = (string) $violation->getMessage();
        }
        self::assertContains($expected, $messages, \sprintf(
            'Expected message "%s" not found in violations: %s',
            $expected,
            implode(', ', $messages)
        ));
    }

    private function formatViolations(iterable $violations): string
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }

        return implode("\n", $messages);
    }
}
