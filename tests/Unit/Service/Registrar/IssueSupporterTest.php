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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar;

use Aeliot\TodoRegistrar\Service\ContextPath\ContextPathBuilderRegistry;
use Aeliot\TodoRegistrar\Service\Registrar\AbstractGeneralIssueConfig;
use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
use Aeliot\TodoRegistrarContracts\TodoInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(IssueSupporter::class)]
final class IssueSupporterTest extends TestCase
{
    private IssueSupporter $issueSupporter;

    protected function setUp(): void
    {
        $contextPathBuilderRegistry = $this->createMock(ContextPathBuilderRegistry::class);
        $this->issueSupporter = new IssueSupporter($contextPathBuilderRegistry);
    }

    #[DataProvider('getSummaryPrefixDataProvider')]
    public function testGetSummaryPrefix(string $tag, ?string $assignee, string $prefix, string $expected): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getTag')->willReturn($tag);
        $todo->method('getAssignee')->willReturn($assignee);

        $config = $this->createMock(AbstractGeneralIssueConfig::class);
        $config->method('getSummaryPrefix')->willReturn($prefix);

        $result = $this->issueSupporter->getSummaryPrefix($todo, $config);

        self::assertSame($expected, $result);
    }

    public static function getSummaryPrefixDataProvider(): array
    {
        return [
            'assignee placeholder with assignee' => ['todo', 'john', '[{assignee}] ', '[john] '],
            'assignee placeholder without assignee' => ['todo', null, '[{assignee}] ', '[] '],
            'assignee placeholder case-insensitive' => ['todo', 'jane', '{ASSIGNEE}: ', 'jane: '],
            'tag placeholder with lowercase tag' => ['todo', null, '[{tag}] ', '[todo] '],
            'tag placeholder with mixed case tag' => ['FiXmE', null, '[{tag}] ', '[FiXmE] '],
            'tag_caps placeholder with lowercase tag' => ['todo', null, '{tag_caps}: ', 'TODO: '],
            'tag_caps placeholder with mixed case tag' => ['FiXmE', null, '{tag_caps}: ', 'FIXME: '],
            'multiple placeholders' => ['todo', null, '[{tag}] {tag_caps}: ', '[todo] TODO: '],
            'case-insensitive tag placeholder uppercase' => ['todo', null, '[{TAG}] ', '[todo] '],
            'case-insensitive tag placeholder mixed' => ['todo', null, '[{Tag}] ', '[todo] '],
            'case-insensitive tag_caps placeholder uppercase' => ['fixme', null, '{TAG_CAPS}: ', 'FIXME: '],
            'case-insensitive tag_caps placeholder mixed' => ['fixme', null, '{Tag_Caps}: ', 'FIXME: '],
            'prefix without placeholders' => ['todo', null, '[TODO] ', '[TODO] '],
            'empty prefix' => ['todo', null, '', ''],
            'only tag placeholder' => ['todo', null, '{tag}', 'todo'],
            'only tag_caps placeholder' => ['todo', null, '{tag_caps}', 'TODO'],
            'multiple placeholders with assignee' => ['fixme', 'bob', '{tag} by {assignee} - {tag_caps}', 'fixme by bob - FIXME'],
        ];
    }

    public function testGetSummary(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getTag')->willReturn('todo');
        $todo->method('getAssignee')->willReturn('john');
        $todo->method('getSummary')->willReturn('Fix the bug');

        $config = $this->createMock(AbstractGeneralIssueConfig::class);
        $config->method('getSummaryPrefix')->willReturn('[{tag}] ');

        $result = $this->issueSupporter->getSummary($todo, $config);

        self::assertSame('[todo] Fix the bug', $result);
    }
}
