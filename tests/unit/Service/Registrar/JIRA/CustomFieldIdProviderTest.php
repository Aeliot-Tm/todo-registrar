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

use Aeliot\TodoRegistrar\Service\Registrar\JIRA\CustomFieldIdFinder;
use Aeliot\TodoRegistrar\Service\Registrar\JIRA\CustomFieldIdProvider;
use Aeliot\TodoRegistrar\Service\Registrar\JIRA\GeneralIssueConfig;
use Aeliot\TodoRegistrar\Service\Registrar\JIRA\InvalidCustomFieldNameException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CustomFieldIdProvider::class)]
final class CustomFieldIdProviderTest extends TestCase
{
    public function testReturnsIdFromConfigMappingWithoutCallingFinder(): void
    {
        $finder = $this->createMock(CustomFieldIdFinder::class);
        $finder->expects(self::never())->method('getId');

        $provider = new CustomFieldIdProvider(
            new GeneralIssueConfig([
                'projectKey' => 'PROJ',
                'issueType' => 'Task',
                'customFieldsMapping' => [
                    'My Custom Field' => 'customfield_123',
                ],
            ]),
            $finder,
        );

        self::assertSame('customfield_123', $provider->getId('My Custom Field'));
    }

    public function testFallsBackToFinderWhenMappingIsMissing(): void
    {
        $finder = $this->createMock(CustomFieldIdFinder::class);
        $finder->expects(self::once())
            ->method('getId')
            ->with('My Custom Field')
            ->willReturn('customfield_123');

        $provider = new CustomFieldIdProvider(
            new GeneralIssueConfig([
                'projectKey' => 'PROJ',
                'issueType' => 'Task',
            ]),
            $finder,
        );

        self::assertSame('customfield_123', $provider->getId('My Custom Field'));
        self::assertSame('customfield_123', $provider->getId('My Custom Field'));
    }

    public function testThrowsWhenFieldIsNotFound(): void
    {
        $finder = $this->createMock(CustomFieldIdFinder::class);
        $finder->method('getId')->willReturn(null);

        $provider = new CustomFieldIdProvider(
            new GeneralIssueConfig([
                'projectKey' => 'PROJ',
                'issueType' => 'Task',
            ]),
            $finder,
        );

        $this->expectException(InvalidCustomFieldNameException::class);
        $this->expectExceptionMessage('Custom field "Unknown field" is not supported');

        $provider->getId('Unknown field');
    }
}
