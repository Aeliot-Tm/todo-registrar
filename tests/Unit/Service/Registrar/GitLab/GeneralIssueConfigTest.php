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

use Aeliot\TodoRegistrar\Service\Registrar\AbstractGeneralIssueConfig;
use Aeliot\TodoRegistrar\Service\Registrar\GitLab\GeneralIssueConfig;
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
        yield 'minimal config' => [['project' => 123]];

        yield 'full config' => [
            [
                'project' => 'owner/repo',
                'addTagToLabels' => true,
                'labels' => ['bug', 'enhancement'],
                'tagPrefix' => 'tag-',
                'summaryPrefix' => '[TODO] ',
                'assignee' => ['user1', 'user@example.com'],
                'milestone' => 'Sprint 1',
                'due_date' => '2025-12-31',
            ],
        ];

        yield 'milestone as int' => [['project' => 123, 'milestone' => 123]];
        yield 'milestone as string' => [['project' => 'owner/repo', 'milestone' => 'v1.0']];
        yield 'milestone as null' => [['project' => 123, 'milestone' => null]];
    }

    public static function getDataForTestValidDueDate(): iterable
    {
        yield 'valid date' => ['2025-12-31'];
        yield 'null date' => [null];
    }

    public static function getDataForTestInvalidDueDate(): iterable
    {
        yield 'invalid format' => ['31-12-2025', 'Option "due_date" must be a date string in format YYYY-MM-DD or null'];
        yield 'text' => ['tomorrow', 'Option "due_date" must be a date string in format YYYY-MM-DD or null'];
        yield 'incomplete' => ['2025-12', 'Option "due_date" must be a date string in format YYYY-MM-DD or null'];
    }

    public static function getDataForTestInvalidMilestone(): iterable
    {
        yield 'array' => [['milestone' => ['invalid']]];
        yield 'boolean' => [['milestone' => true]];
    }

    #[DataProvider('getDataForTestValidConfig')]
    public function testValidConfig(array $config): void
    {
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    #[DataProvider('getDataForTestValidDueDate')]
    public function testValidDueDate(?string $dueDate): void
    {
        $config = ['project' => 123, 'due_date' => $dueDate];
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    #[DataProvider('getDataForTestInvalidDueDate')]
    public function testInvalidDueDate(string $dueDate, string $expectedMessagePart): void
    {
        $config = ['project' => 123, 'due_date' => $dueDate];
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessagePart($expectedMessagePart, $violations);
    }

    #[DataProvider('getDataForTestInvalidMilestone')]
    public function testInvalidMilestone(array $config): void
    {
        $config['project'] = $config['project'] ?? 123;
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
    }

    public function testInvalidAssigneeElementType(): void
    {
        // Note: 'assignee' as string is cast to array by normalization
        $config = ['project' => 123, 'assignee' => [123]];
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Each assignee must be a string (username or email)', $violations);
    }

    public function testUnknownOptionsDetected(): void
    {
        $config = ['project' => 123, 'unknown_option' => 'value'];
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

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

    public function testValueAssigning(): void
    {
        $config = [
            'project' => 'owner/repo',
            'addTagToLabels' => true,
            'labels' => ['label1'],
            'assignee' => ['user1', 'user@example.com'],
            'milestone' => 'Sprint 1',
            'due_date' => '2025-12-31',
        ];
        $generalConfig = new GeneralIssueConfig($config);

        self::assertSame('owner/repo', $generalConfig->getProject());
        self::assertTrue($generalConfig->isAddTagToLabels());
        self::assertSame(['label1'], $generalConfig->getLabels());
        self::assertSame(['user1', 'user@example.com'], $generalConfig->getAssignee());
        self::assertSame('Sprint 1', $generalConfig->getMilestone());
        self::assertSame('2025-12-31', $generalConfig->getDueDate());
    }

    public function testDefaultValues(): void
    {
        $generalConfig = new GeneralIssueConfig(['project' => 123]);

        self::assertSame(123, $generalConfig->getProject());
        self::assertFalse($generalConfig->isAddTagToLabels());
        self::assertSame([], $generalConfig->getLabels());
        self::assertSame('', $generalConfig->getTagPrefix());
        self::assertSame('', $generalConfig->getSummaryPrefix());
        self::assertSame([], $generalConfig->getAssignee());
        self::assertNull($generalConfig->getMilestone());
        self::assertNull($generalConfig->getDueDate());
    }

    public function testEmptyDueDateBecomesNull(): void
    {
        $generalConfig = new GeneralIssueConfig(['project' => 123, 'due_date' => '']);
        self::assertNull($generalConfig->getDueDate());

        $violations = self::$validator->validate($generalConfig);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testInvalidLabelsElementType(): void
    {
        $generalConfig = new GeneralIssueConfig(['project' => 123, 'labels' => [123, 'valid']]);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Each label must be a string', $violations);
    }

    public function testEmptyAssigneeFiltered(): void
    {
        // Empty strings in assignee array are filtered out (array_filter keeps keys)
        $generalConfig = new GeneralIssueConfig(['project' => 123, 'assignee' => ['', 'user1', '']]);
        self::assertSame([1 => 'user1'], $generalConfig->getAssignee());
    }

    public function testMilestoneAsInteger(): void
    {
        $generalConfig = new GeneralIssueConfig(['project' => 123, 'milestone' => 42]);
        self::assertSame(42, $generalConfig->getMilestone());

        $violations = self::$validator->validate($generalConfig);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testInvalidTagPrefixType(): void
    {
        $generalConfig = new GeneralIssueConfig(['project' => 123, 'tagPrefix' => 123]);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "tagPrefix" must be a string', $violations);
    }

    public function testInvalidSummaryPrefixType(): void
    {
        $generalConfig = new GeneralIssueConfig(['project' => 123, 'summaryPrefix' => 123]);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "summaryPrefix" must be a string', $violations);
    }

    public function testProjectAsInteger(): void
    {
        $generalConfig = new GeneralIssueConfig(['project' => 123]);
        self::assertSame(123, $generalConfig->getProject());

        $violations = self::$validator->validate($generalConfig);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testProjectAsString(): void
    {
        $generalConfig = new GeneralIssueConfig(['project' => 'owner/repo']);
        self::assertSame('owner/repo', $generalConfig->getProject());

        $violations = self::$validator->validate($generalConfig);
        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testProjectIsRequired(): void
    {
        $generalConfig = new GeneralIssueConfig([]);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "project" is required for GitLab registrar', $violations);
    }

    public function testProjectCannotBeNull(): void
    {
        $generalConfig = new GeneralIssueConfig(['project' => null]);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "project" is required for GitLab registrar', $violations);
    }

    public function testProjectInvalidType(): void
    {
        $generalConfig = new GeneralIssueConfig(['project' => [123]]);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessagePart('Option "project" must be an integer ID or string name', $violations);
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

    private static function assertContainsMessagePart(string $expectedPart, iterable $violations): void
    {
        $messages = [];
        foreach ($violations as $violation) {
            $message = (string) $violation->getMessage();
            $messages[] = $message;
            if (str_contains($message, $expectedPart)) {
                return;
            }
        }
        self::fail(\sprintf(
            'Expected message part "%s" not found in violations: %s',
            $expectedPart,
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
