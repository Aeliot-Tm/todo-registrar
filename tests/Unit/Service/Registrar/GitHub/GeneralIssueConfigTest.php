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

use Aeliot\TodoRegistrar\Service\Registrar\AbstractGeneralIssueConfig;
use Aeliot\TodoRegistrar\Service\Registrar\GitHub\GeneralIssueConfig;
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
        yield 'minimal config' => [[]];

        yield 'full config' => [
            [
                'addTagToLabels' => true,
                'labels' => ['bug', 'enhancement'],
                'tagPrefix' => 'tag-',
                'summaryPrefix' => '[TODO] ',
                'assignees' => ['user1', 'user2'],
            ],
        ];

        yield 'empty arrays' => [
            [
                'labels' => [],
                'assignees' => [],
            ],
        ];
    }

    public static function getDataForTestInvalidLabelsType(): iterable
    {
        // Note: 'labels is string' - normalization casts to array, so no error
        yield 'labels contains int' => [['labels' => [123]], 'Each label must be a string'];
        yield 'labels contains mixed' => [['labels' => ['valid', 123]], 'Each label must be a string'];
    }

    public static function getDataForTestInvalidAssigneesType(): iterable
    {
        yield 'assignees is string' => [['assignees' => 'not-an-array'], 'Option "assignees" must be an array'];
        yield 'assignees contains int' => [['assignees' => [123]], 'Each assignee must be a string GitHub username'];
    }

    #[DataProvider('getDataForTestValidConfig')]
    public function testValidConfig(array $config): void
    {
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertCount(0, $violations, $this->formatViolations($violations));
    }

    #[DataProvider('getDataForTestInvalidLabelsType')]
    public function testInvalidLabelsType(array $config, string $expectedMessage): void
    {
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage($expectedMessage, $violations);
    }

    #[DataProvider('getDataForTestInvalidAssigneesType')]
    public function testInvalidAssigneesType(array $config, string $expectedMessage): void
    {
        $generalConfig = new GeneralIssueConfig($config);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage($expectedMessage, $violations);
    }

    public function testUnknownOptionsDetected(): void
    {
        $config = ['unknown_option' => 'value', 'another_unknown' => 123];
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
            'addTagToLabels' => true,
            'labels' => ['label1', 'label2'],
            'tagPrefix' => 'prefix-',
            'summaryPrefix' => 'Summary: ',
            'assignees' => ['user1', 'user2'],
        ];
        $generalConfig = new GeneralIssueConfig($config);

        self::assertTrue($generalConfig->isAddTagToLabels());
        self::assertSame(['label1', 'label2'], $generalConfig->getLabels());
        self::assertSame('prefix-', $generalConfig->getTagPrefix());
        self::assertSame('Summary: ', $generalConfig->getSummaryPrefix());
        self::assertSame(['user1', 'user2'], $generalConfig->getAssignees());
    }

    public function testDefaultValues(): void
    {
        $generalConfig = new GeneralIssueConfig([]);

        self::assertFalse($generalConfig->isAddTagToLabels());
        self::assertSame([], $generalConfig->getLabels());
        self::assertSame('', $generalConfig->getTagPrefix());
        self::assertSame('', $generalConfig->getSummaryPrefix());
        self::assertSame([], $generalConfig->getAssignees());
    }

    public function testAddTagToLabelsNormalization(): void
    {
        // Truthy value becomes true
        $generalConfig = new GeneralIssueConfig(['addTagToLabels' => 1]);
        self::assertTrue($generalConfig->isAddTagToLabels());

        // Falsy value becomes false
        $generalConfig = new GeneralIssueConfig(['addTagToLabels' => 0]);
        self::assertFalse($generalConfig->isAddTagToLabels());

        // String "1" becomes true
        $generalConfig = new GeneralIssueConfig(['addTagToLabels' => '1']);
        self::assertTrue($generalConfig->isAddTagToLabels());

        // Empty string becomes false
        $generalConfig = new GeneralIssueConfig(['addTagToLabels' => '']);
        self::assertFalse($generalConfig->isAddTagToLabels());

        // Array becomes true (non-empty)
        $generalConfig = new GeneralIssueConfig(['addTagToLabels' => ['something']]);
        self::assertTrue($generalConfig->isAddTagToLabels());

        // Empty array becomes false
        $generalConfig = new GeneralIssueConfig(['addTagToLabels' => []]);
        self::assertFalse($generalConfig->isAddTagToLabels());
    }

    public function testLabelsNormalization(): void
    {
        // Single string becomes array
        $generalConfig = new GeneralIssueConfig(['labels' => 'single-label']);
        self::assertSame(['single-label'], $generalConfig->getLabels());
    }

    public function testInvalidTagPrefixType(): void
    {
        $generalConfig = new GeneralIssueConfig(['tagPrefix' => 123]);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "tagPrefix" must be a string', $violations);
    }

    public function testInvalidSummaryPrefixType(): void
    {
        $generalConfig = new GeneralIssueConfig(['summaryPrefix' => 123]);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        self::assertContainsMessage('Option "summaryPrefix" must be a string', $violations);
    }

    public function testMultipleUnknownOptionsInMessage(): void
    {
        $generalConfig = new GeneralIssueConfig([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $violations = self::$validator->validate($generalConfig);

        self::assertGreaterThan(0, \count($violations));
        $hasError = false;
        foreach ($violations as $violation) {
            $message = (string) $violation->getMessage();
            if (str_contains($message, 'foo') && str_contains($message, 'baz')) {
                $hasError = true;
                break;
            }
        }
        self::assertTrue($hasError, 'Expected validation error to list multiple unknown options');
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
