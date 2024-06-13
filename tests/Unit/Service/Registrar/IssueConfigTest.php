<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar;

use Aeliot\TodoRegistrar\Service\Registrar\JIRA\IssueConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IssueConfig::class)]
final class IssueConfigTest extends TestCase
{
    public function testValueAssigning(): void
    {
        $values = [
            'issue' => [
                'addTagToLabels' => true,
                'components' => ['Component-1', 'Component-2'],
                'labels' => ['Label-1', 'Label-2'],
                'tagPrefix' => 'tag-',
                'type' => 'Bug',
            ],
            'projectKey' => 'Todo',
        ];
        $config = new IssueConfig($values);

        self::assertTrue($config->isAddTagToLabels());
        self::assertSame('Bug', $config->getIssueType());
        self::assertSame('tag-', $config->getTagPrefix());
        self::assertSame(['Component-1', 'Component-2'], $config->getComponents());
        self::assertSame(['Label-1', 'Label-2'], $config->getLabels());
        self::assertSame('Todo', $config->getProjectKey());
    }
}