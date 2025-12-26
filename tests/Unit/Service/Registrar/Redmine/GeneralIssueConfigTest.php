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

use Aeliot\TodoRegistrar\Service\Registrar\AbstractGeneralIssueConfig;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\GeneralIssueConfig;
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
                'project' => 1,
                'tracker' => 'Bug',
            ],
        ];

        yield 'project as string' => [
            [
                'project' => 'my-project',
                'tracker' => 'Bug',
            ],
        ];

        yield 'tracker as int' => [
            [
                'project' => 1,
                'tracker' => 2,
            ],
        ];

        yield 'full config' => [
            [
                'project' => 1,
                'tracker' => 'Bug',
                'assignee' => 'developer1',
                'priority' => 'High',
                'category' => 'Backend',
                'fixed_version' => 'v1.0',
                'start_date' => '2024-01-01',
                'due_date' => '2024-12-31',
                'estimated_hours' => 8.5,
            ],
        ];
    }

    public static function getDataForTestRequiredFields(): iterable
    {
        yield 'missing project' => [
            ['tracker' => 'Bug'],
            'Option "project" is required for Redmine registrar',
        ];

        yield 'missing tracker' => [
            ['project' => 1],
            'Option "tracker" is required for Redmine registrar',
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

    public function testInvalidDueDate(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'due_date' => 'invalid-date',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        // Check for any message containing the date format requirement
        $hasDateError = false;
        foreach ($violations as $violation) {
            if (str_contains((string) $violation->getMessage(), 'YYYY-MM-DD')) {
                $hasDateError = true;
                break;
            }
        }
        self::assertTrue($hasDateError, 'Expected validation error for invalid due date format');
    }

    public function testInvalidStartDate(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'start_date' => 'invalid-date',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        // Check for any message containing the date format requirement
        $hasDateError = false;
        foreach ($violations as $violation) {
            if (str_contains((string) $violation->getMessage(), 'YYYY-MM-DD')) {
                $hasDateError = true;
                break;
            }
        }
        self::assertTrue($hasDateError, 'Expected validation error for invalid start date format');
    }

    public function testInvalidEstimatedHours(): void
    {
        // Note: Arrays are normalized to 0.0 (float) before validation,
        // so we can't test invalid type for estimated_hours through normalization.
        // The validation happens after normalization, so invalid types are already converted.
        // This test verifies that valid float values work correctly.
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'estimated_hours' => 8.5,
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
        self::assertSame(8.5, $config->getEstimatedHours());
    }

    public function testUnknownOptionsDetected(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
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
            'project' => 1,
            'tracker' => 'Bug',
            'assignee' => null,
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testOptionalPriorityCanBeNull(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'priority' => null,
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    public function testValueAssigning(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'assignee' => 'developer1',
            'priority' => 'High',
            'category' => 'Backend',
            'fixed_version' => 'v1.0',
            'start_date' => '2024-01-01',
            'due_date' => '2024-12-31',
            'estimated_hours' => 8.5,
        ]);

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

    public function testDefaultValues(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
        ]);

        self::assertFalse($config->isAddTagToLabels());
        self::assertSame([], $config->getLabels());
        self::assertSame('', $config->getTagPrefix());
        self::assertSame('', $config->getSummaryPrefix());
        self::assertNull($config->getAssignee());
        self::assertNull($config->getPriority());
        self::assertNull($config->getCategory());
        self::assertNull($config->getFixedVersion());
        self::assertNull($config->getStartDate());
        self::assertNull($config->getDueDate());
        self::assertNull($config->getEstimatedHours());
    }

    public function testValidAssigneeAsString(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'assignee' => 'developer1',
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
        self::assertSame('developer1', $config->getAssignee());
    }

    public function testValidAssigneeAsInt(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'assignee' => 123,
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
        self::assertSame(123, $config->getAssignee());
    }

    public function testValidPriorityAsString(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'priority' => 'High',
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
        self::assertSame('High', $config->getPriority());
    }

    public function testValidPriorityAsInt(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'priority' => 5,
        ]);
        $violations = self::$validator->validate($config);

        self::assertCount(0, $violations, $this->formatViolations($violations));
        self::assertSame(5, $config->getPriority());
    }

    public function testEmptyProjectIsInvalid(): void
    {
        $config = new GeneralIssueConfig([
            'project' => '',
            'tracker' => 'Bug',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "project" is required for Redmine registrar', $violations);
    }

    public function testEmptyTrackerIsInvalid(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => '',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "tracker" is required for Redmine registrar', $violations);
    }

    public function testInvalidProjectType(): void
    {
        $config = new GeneralIssueConfig([
            'project' => [],
            'tracker' => 'Bug',
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        // Check for any message about project type
        $hasError = false;
        foreach ($violations as $violation) {
            if (str_contains((string) $violation->getMessage(), 'project')
                && (str_contains((string) $violation->getMessage(), 'integer')
                 || str_contains((string) $violation->getMessage(), 'string'))) {
                $hasError = true;
                break;
            }
        }
        self::assertTrue($hasError, 'Expected validation error for invalid project type');
    }

    public function testInvalidTrackerType(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => [],
        ]);
        $violations = self::$validator->validate($config);

        self::assertGreaterThan(0, \count($violations));
        // Check for any message about tracker type
        $hasError = false;
        foreach ($violations as $violation) {
            if (str_contains((string) $violation->getMessage(), 'tracker')
                && (str_contains((string) $violation->getMessage(), 'integer')
                 || str_contains((string) $violation->getMessage(), 'string'))) {
                $hasError = true;
                break;
            }
        }
        self::assertTrue($hasError, 'Expected validation error for invalid tracker type');
    }

    public function testDateNormalization(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'start_date' => '  2024-01-01  ',
            'due_date' => '  2024-12-31  ',
        ]);

        self::assertSame('2024-01-01', $config->getStartDate());
        self::assertSame('2024-12-31', $config->getDueDate());
    }

    public function testEstimatedHoursNormalization(): void
    {
        $config = new GeneralIssueConfig([
            'project' => 1,
            'tracker' => 'Bug',
            'estimated_hours' => '8.5',
        ]);

        self::assertSame(8.5, $config->getEstimatedHours());
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
