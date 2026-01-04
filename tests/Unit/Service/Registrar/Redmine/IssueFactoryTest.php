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

use Aeliot\TodoRegistrar\Dto\InlineConfig\InlineConfig;
use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\EntityResolver;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\GeneralIssueConfig;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\IssueFactory;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\UserResolver;
use Aeliot\TodoRegistrarContracts\TodoInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IssueFactory::class)]
final class IssueFactoryTest extends TestCase
{
    public function testCreateIssueWithMinimalConfig(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([]));
        $todo->method('getAssignee')->willReturn(null);

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('');
        $generalConfig->method('getProjectIdentifier')->willReturn(1);
        $generalConfig->method('getTracker')->willReturn(2);
        $generalConfig->method('getAssignee')->willReturn(null);
        $generalConfig->method('getPriority')->willReturn(null);
        $generalConfig->method('getCategory')->willReturn(null);
        $generalConfig->method('getFixedVersion')->willReturn(null);
        $generalConfig->method('getStartDate')->willReturn(null);
        $generalConfig->method('getDueDate')->willReturn(null);
        $generalConfig->method('getEstimatedHours')->willReturn(null);

        $userResolver = $this->createMock(UserResolver::class);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(1)->willReturn(1);
        $entityResolver->method('resolveTrackerId')->with(2)->willReturn(2);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);
        $issue = $factory->create($todo);

        $data = $issue->getData();
        self::assertSame('Test Summary', $data['subject']);
        self::assertSame('Test Description', $data['description']);
        self::assertSame(1, $data['project_id']);
        self::assertSame(2, $data['tracker_id']);
    }

    public function testCreateIssueWithSummaryPrefix(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([]));
        $todo->method('getAssignee')->willReturn(null);

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('[TODO] ');
        $generalConfig->method('getProjectIdentifier')->willReturn(1);
        $generalConfig->method('getTracker')->willReturn(2);
        $generalConfig->method('getAssignee')->willReturn(null);
        $generalConfig->method('getPriority')->willReturn(null);
        $generalConfig->method('getCategory')->willReturn(null);
        $generalConfig->method('getFixedVersion')->willReturn(null);
        $generalConfig->method('getStartDate')->willReturn(null);
        $generalConfig->method('getDueDate')->willReturn(null);
        $generalConfig->method('getEstimatedHours')->willReturn(null);

        $userResolver = $this->createMock(UserResolver::class);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(1)->willReturn(1);
        $entityResolver->method('resolveTrackerId')->with(2)->willReturn(2);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);
        $issue = $factory->create($todo);

        $data = $issue->getData();
        self::assertSame('[TODO] Test Summary', $data['subject']);
    }

    public function testCreateIssueWithAllFields(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([]));
        $todo->method('getAssignee')->willReturn(null);

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('');
        $generalConfig->method('getProjectIdentifier')->willReturn(1);
        $generalConfig->method('getTracker')->willReturn(2);
        $generalConfig->method('getAssignee')->willReturn('developer1');
        $generalConfig->method('getPriority')->willReturn('High');
        $generalConfig->method('getCategory')->willReturn('Backend');
        $generalConfig->method('getFixedVersion')->willReturn('v1.0');
        $generalConfig->method('getStartDate')->willReturn('2024-01-01');
        $generalConfig->method('getDueDate')->willReturn('2024-12-31');
        $generalConfig->method('getEstimatedHours')->willReturn(8.5);

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->method('resolveUserId')->with('developer1')->willReturn(42);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(1)->willReturn(1);
        $entityResolver->method('resolveTrackerId')->with(2)->willReturn(2);
        $entityResolver->method('resolvePriorityId')->with('High')->willReturn(3);
        $entityResolver->method('resolveCategoryId')->with('Backend')->willReturn(7);
        $entityResolver->method('resolveVersionId')->with('v1.0')->willReturn(10);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);
        $issue = $factory->create($todo);

        $data = $issue->getData();
        self::assertSame('Test Summary', $data['subject']);
        self::assertSame('Test Description', $data['description']);
        self::assertSame(1, $data['project_id']);
        self::assertSame(2, $data['tracker_id']);
        self::assertSame(42, $data['assigned_to_id']);
        self::assertSame(3, $data['priority_id']);
        self::assertSame(7, $data['category_id']);
        self::assertSame(10, $data['fixed_version_id']);
        self::assertSame('2024-01-01', $data['start_date']);
        self::assertSame('2024-12-31', $data['due_date']);
        self::assertSame(8.5, $data['estimated_hours']);
    }

    public function testCreateIssueWithInlineConfig(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([
            'tracker' => 'Task',
            'priority' => 'Low',
            'assignee' => 'user1',
        ]));
        $todo->method('getAssignee')->willReturn(null);

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('');
        $generalConfig->method('getProjectIdentifier')->willReturn(1);
        $generalConfig->method('getTracker')->willReturn('Bug');
        $generalConfig->method('getAssignee')->willReturn(null);
        $generalConfig->method('getPriority')->willReturn('High');
        $generalConfig->method('getCategory')->willReturn(null);
        $generalConfig->method('getFixedVersion')->willReturn(null);
        $generalConfig->method('getStartDate')->willReturn(null);
        $generalConfig->method('getDueDate')->willReturn(null);
        $generalConfig->method('getEstimatedHours')->willReturn(null);

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->method('resolveUserId')->with('user1')->willReturn(50);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(1)->willReturn(1);
        $entityResolver->method('resolveTrackerId')->with('Task')->willReturn(4);
        $entityResolver->method('resolvePriorityId')->with('Low')->willReturn(1);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);
        $issue = $factory->create($todo);

        $data = $issue->getData();
        self::assertSame(4, $data['tracker_id']);
        self::assertSame(1, $data['priority_id']);
        self::assertSame(50, $data['assigned_to_id']);
    }

    public function testCreateIssueThrowsExceptionWhenProjectNotFound(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([]));
        $todo->method('getAssignee')->willReturn(null);

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('');
        $generalConfig->method('getProjectIdentifier')->willReturn(999);

        $userResolver = $this->createMock(UserResolver::class);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(999)->willReturn(null);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Project "999" not found');

        $factory->create($todo);
    }

    public function testCreateIssueThrowsExceptionWhenTrackerNotSpecified(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([]));
        $todo->method('getAssignee')->willReturn(null);

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('');
        $generalConfig->method('getProjectIdentifier')->willReturn(1);
        $generalConfig->method('getTracker')->willReturn(null);

        $userResolver = $this->createMock(UserResolver::class);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(1)->willReturn(1);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tracker must be specified in config or inline config');

        $factory->create($todo);
    }

    public function testCreateIssueThrowsExceptionWhenTrackerNotFound(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([]));
        $todo->method('getAssignee')->willReturn(null);

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('');
        $generalConfig->method('getProjectIdentifier')->willReturn(1);
        $generalConfig->method('getTracker')->willReturn('NonExistent');

        $userResolver = $this->createMock(UserResolver::class);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(1)->willReturn(1);
        $entityResolver->method('resolveTrackerId')->with('NonExistent')->willReturn(null);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tracker "NonExistent" not found');

        $factory->create($todo);
    }

    public function testCreateIssueUsesTagAssignee(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([]));
        $todo->method('getAssignee')->willReturn('tag-assignee');

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('');
        $generalConfig->method('getProjectIdentifier')->willReturn(1);
        $generalConfig->method('getTracker')->willReturn(2);
        $generalConfig->method('getAssignee')->willReturn(null);
        $generalConfig->method('getPriority')->willReturn(null);
        $generalConfig->method('getCategory')->willReturn(null);
        $generalConfig->method('getFixedVersion')->willReturn(null);
        $generalConfig->method('getStartDate')->willReturn(null);
        $generalConfig->method('getDueDate')->willReturn(null);
        $generalConfig->method('getEstimatedHours')->willReturn(null);

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->method('resolveUserId')->with('tag-assignee')->willReturn(60);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(1)->willReturn(1);
        $entityResolver->method('resolveTrackerId')->with(2)->willReturn(2);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);
        $issue = $factory->create($todo);

        $data = $issue->getData();
        self::assertSame(60, $data['assigned_to_id']);
    }

    public function testCreateIssueDoesNotSetAssigneeWhenResolverReturnsNull(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([]));
        $todo->method('getAssignee')->willReturn('nonexistent-user');

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('');
        $generalConfig->method('getProjectIdentifier')->willReturn(1);
        $generalConfig->method('getTracker')->willReturn(2);
        $generalConfig->method('getAssignee')->willReturn(null);
        $generalConfig->method('getPriority')->willReturn(null);
        $generalConfig->method('getCategory')->willReturn(null);
        $generalConfig->method('getFixedVersion')->willReturn(null);
        $generalConfig->method('getStartDate')->willReturn(null);
        $generalConfig->method('getDueDate')->willReturn(null);
        $generalConfig->method('getEstimatedHours')->willReturn(null);

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->method('resolveUserId')->with('nonexistent-user')->willReturn(null);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(1)->willReturn(1);
        $entityResolver->method('resolveTrackerId')->with(2)->willReturn(2);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);
        $issue = $factory->create($todo);

        $data = $issue->getData();
        self::assertArrayNotHasKey('assigned_to_id', $data);
    }

    public function testCreateIssueDoesNotSetPriorityWhenResolverReturnsNull(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([]));
        $todo->method('getAssignee')->willReturn(null);

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('');
        $generalConfig->method('getProjectIdentifier')->willReturn(1);
        $generalConfig->method('getTracker')->willReturn(2);
        $generalConfig->method('getAssignee')->willReturn(null);
        $generalConfig->method('getPriority')->willReturn('NonexistentPriority');
        $generalConfig->method('getCategory')->willReturn(null);
        $generalConfig->method('getFixedVersion')->willReturn(null);
        $generalConfig->method('getStartDate')->willReturn(null);
        $generalConfig->method('getDueDate')->willReturn(null);
        $generalConfig->method('getEstimatedHours')->willReturn(null);

        $userResolver = $this->createMock(UserResolver::class);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(1)->willReturn(1);
        $entityResolver->method('resolveTrackerId')->with(2)->willReturn(2);
        $entityResolver->method('resolvePriorityId')->with('NonexistentPriority')->willReturn(null);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);
        $issue = $factory->create($todo);

        $data = $issue->getData();
        self::assertArrayNotHasKey('priority_id', $data);
    }

    public function testCreateIssueDoesNotSetCategoryWhenResolverReturnsNull(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $todo->method('getSummary')->willReturn('Test Summary');
        $todo->method('getDescription')->willReturn('Test Description');
        $todo->method('getInlineConfig')->willReturn(new InlineConfig([]));
        $todo->method('getAssignee')->willReturn(null);

        $generalConfig = $this->createMock(GeneralIssueConfig::class);
        $generalConfig->method('getSummaryPrefix')->willReturn('');
        $generalConfig->method('getProjectIdentifier')->willReturn(1);
        $generalConfig->method('getTracker')->willReturn(2);
        $generalConfig->method('getAssignee')->willReturn(null);
        $generalConfig->method('getPriority')->willReturn(null);
        $generalConfig->method('getCategory')->willReturn('NonexistentCategory');
        $generalConfig->method('getFixedVersion')->willReturn(null);
        $generalConfig->method('getStartDate')->willReturn(null);
        $generalConfig->method('getDueDate')->willReturn(null);
        $generalConfig->method('getEstimatedHours')->willReturn(null);

        $userResolver = $this->createMock(UserResolver::class);

        $entityResolver = $this->createMock(EntityResolver::class);
        $entityResolver->method('resolveProjectId')->with(1)->willReturn(1);
        $entityResolver->method('resolveTrackerId')->with(2)->willReturn(2);
        $entityResolver->method('resolveCategoryId')->with('NonexistentCategory')->willReturn(null);

        $factory = new IssueFactory($entityResolver, $generalConfig, new IssueSupporter(), $userResolver);
        $issue = $factory->create($todo);

        $data = $issue->getData();
        self::assertArrayNotHasKey('category_id', $data);
    }
}
