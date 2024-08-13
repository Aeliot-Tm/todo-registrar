<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar;

use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Service\Registrar\JIRA\IssueConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(IssueConfig::class)]
final class IssueConfigTest extends TestCase
{
    public static function getDataForTestIssueTypeAlias(): iterable
    {
        yield [
            [
                'projectKey' => 'any string',
                'issueType' => 'Bug',
            ],
        ];

        yield [
            [
                'projectKey' => 'any string',
                'type' => 'Bug',
            ],
        ];
    }

    public static function getDataForTestThrowExceptionWhenMissedRequiredProperty(): iterable
    {
        $fullConfig = [
            'projectKey' => 'any string',
            'type' => 'any string',
        ];

        foreach (array_keys($fullConfig) as $key) {
            $config = $fullConfig;
            unset($config[$key]);
            yield [$config];
        }
    }

    #[DataProvider('getDataForTestIssueTypeAlias')]
    public function testIssueTypeAlias(array $values): void
    {
        $config = new IssueConfig($values);
        self::assertSame('Bug', $config->getIssueType());
    }

    public function testThrowExceptionWithIssueTypeDuplicatedByAlias(): void
    {
        $this->expectException(InvalidConfigException::class);
        $values = [
            'projectKey' => 'any string',
            'type' => 'any string',
            'issueType' => 'any string',
        ];
        new IssueConfig($values);
    }

    #[DataProvider('getDataForTestThrowExceptionWhenMissedRequiredProperty')]
    public function testThrowExceptionWhenMissedRequiredProperty(array $values): void
    {
        $this->expectException(InvalidConfigException::class);
        new IssueConfig($values);
    }

    public function testValueAssigning(): void
    {
        $values = [
            'addTagToLabels' => true,
            'components' => ['Component-1', 'Component-2'],
            'labels' => ['Label-1', 'Label-2'],
            'priority' => 'Low',
            'summaryPrefix' => 'a-summary-prefix. ',
            'tagPrefix' => 'tag-',
            'type' => 'Bug',
            // extra key added in factory
            'projectKey' => 'Todo',
        ];
        $config = new IssueConfig($values);

        self::assertTrue($config->isAddTagToLabels());
        self::assertSame('Bug', $config->getIssueType());
        self::assertSame('Low', $config->getPriority());
        self::assertSame('a-summary-prefix. ', $config->getSummaryPrefix());
        self::assertSame('tag-', $config->getTagPrefix());
        self::assertSame(['Component-1', 'Component-2'], $config->getComponents());
        self::assertSame(['Label-1', 'Label-2'], $config->getLabels());
        self::assertSame('Todo', $config->getProjectKey());
    }
}
