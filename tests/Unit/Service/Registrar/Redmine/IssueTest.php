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

use Aeliot\TodoRegistrar\Service\Registrar\Redmine\Issue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Issue::class)]
final class IssueTest extends TestCase
{
    public function testGetDataReturnsEmptyArrayInitially(): void
    {
        $issue = new Issue();

        self::assertSame([], $issue->getData());
    }

    public function testSetSubject(): void
    {
        $issue = new Issue();
        $issue->setSubject('Test Subject');

        $data = $issue->getData();
        self::assertSame('Test Subject', $data['subject']);
    }

    public function testSetDescription(): void
    {
        $issue = new Issue();
        $issue->setDescription('Test Description');

        $data = $issue->getData();
        self::assertSame('Test Description', $data['description']);
    }

    public function testSetProjectId(): void
    {
        $issue = new Issue();
        $issue->setProjectId(123);

        $data = $issue->getData();
        self::assertSame(123, $data['project_id']);
    }

    public function testSetTrackerId(): void
    {
        $issue = new Issue();
        $issue->setTrackerId(5);

        $data = $issue->getData();
        self::assertSame(5, $data['tracker_id']);
    }

    public function testSetAssignedToId(): void
    {
        $issue = new Issue();
        $issue->setAssignedToId(42);

        $data = $issue->getData();
        self::assertSame(42, $data['assigned_to_id']);
    }

    public function testSetAssignedToIdWithNull(): void
    {
        $issue = new Issue();
        $issue->setAssignedToId(null);

        $data = $issue->getData();
        self::assertArrayNotHasKey('assigned_to_id', $data);
    }

    public function testSetPriorityId(): void
    {
        $issue = new Issue();
        $issue->setPriorityId(3);

        $data = $issue->getData();
        self::assertSame(3, $data['priority_id']);
    }

    public function testSetPriorityIdWithNull(): void
    {
        $issue = new Issue();
        $issue->setPriorityId(null);

        $data = $issue->getData();
        self::assertArrayNotHasKey('priority_id', $data);
    }

    public function testSetCategoryId(): void
    {
        $issue = new Issue();
        $issue->setCategoryId(7);

        $data = $issue->getData();
        self::assertSame(7, $data['category_id']);
    }

    public function testSetCategoryIdWithNull(): void
    {
        $issue = new Issue();
        $issue->setCategoryId(null);

        $data = $issue->getData();
        self::assertArrayNotHasKey('category_id', $data);
    }

    public function testSetFixedVersionId(): void
    {
        $issue = new Issue();
        $issue->setFixedVersionId(10);

        $data = $issue->getData();
        self::assertSame(10, $data['fixed_version_id']);
    }

    public function testSetFixedVersionIdWithNull(): void
    {
        $issue = new Issue();
        $issue->setFixedVersionId(null);

        $data = $issue->getData();
        self::assertArrayNotHasKey('fixed_version_id', $data);
    }

    public function testSetStartDate(): void
    {
        $issue = new Issue();
        $issue->setStartDate('2024-01-01');

        $data = $issue->getData();
        self::assertSame('2024-01-01', $data['start_date']);
    }

    public function testSetStartDateWithNull(): void
    {
        $issue = new Issue();
        $issue->setStartDate(null);

        $data = $issue->getData();
        self::assertArrayNotHasKey('start_date', $data);
    }

    public function testSetStartDateWithEmptyString(): void
    {
        $issue = new Issue();
        $issue->setStartDate('');

        $data = $issue->getData();
        self::assertArrayNotHasKey('start_date', $data);
    }

    public function testSetDueDate(): void
    {
        $issue = new Issue();
        $issue->setDueDate('2024-12-31');

        $data = $issue->getData();
        self::assertSame('2024-12-31', $data['due_date']);
    }

    public function testSetDueDateWithNull(): void
    {
        $issue = new Issue();
        $issue->setDueDate(null);

        $data = $issue->getData();
        self::assertArrayNotHasKey('due_date', $data);
    }

    public function testSetDueDateWithEmptyString(): void
    {
        $issue = new Issue();
        $issue->setDueDate('');

        $data = $issue->getData();
        self::assertArrayNotHasKey('due_date', $data);
    }

    public function testSetEstimatedHours(): void
    {
        $issue = new Issue();
        $issue->setEstimatedHours(8.5);

        $data = $issue->getData();
        self::assertSame(8.5, $data['estimated_hours']);
    }

    public function testSetEstimatedHoursWithNull(): void
    {
        $issue = new Issue();
        $issue->setEstimatedHours(null);

        $data = $issue->getData();
        self::assertArrayNotHasKey('estimated_hours', $data);
    }

    public function testMultipleFields(): void
    {
        $issue = new Issue();
        $issue->setSubject('Test Subject');
        $issue->setDescription('Test Description');
        $issue->setProjectId(123);
        $issue->setTrackerId(5);
        $issue->setAssignedToId(42);
        $issue->setPriorityId(3);
        $issue->setCategoryId(7);
        $issue->setFixedVersionId(10);
        $issue->setStartDate('2024-01-01');
        $issue->setDueDate('2024-12-31');
        $issue->setEstimatedHours(8.5);

        $data = $issue->getData();
        self::assertSame('Test Subject', $data['subject']);
        self::assertSame('Test Description', $data['description']);
        self::assertSame(123, $data['project_id']);
        self::assertSame(5, $data['tracker_id']);
        self::assertSame(42, $data['assigned_to_id']);
        self::assertSame(3, $data['priority_id']);
        self::assertSame(7, $data['category_id']);
        self::assertSame(10, $data['fixed_version_id']);
        self::assertSame('2024-01-01', $data['start_date']);
        self::assertSame('2024-12-31', $data['due_date']);
        self::assertSame(8.5, $data['estimated_hours']);
    }

    public function testSetSubjectOverwritesPreviousValue(): void
    {
        $issue = new Issue();
        $issue->setSubject('First Subject');
        $issue->setSubject('Second Subject');

        $data = $issue->getData();
        self::assertSame('Second Subject', $data['subject']);
        self::assertCount(1, $data);
    }

    public function testSetAssignedToIdOverwritesPreviousValue(): void
    {
        $issue = new Issue();
        $issue->setAssignedToId(42);
        $issue->setAssignedToId(100);

        $data = $issue->getData();
        self::assertSame(100, $data['assigned_to_id']);
    }

    public function testSetAssignedToIdWithNullDoesNotAddKey(): void
    {
        $issue = new Issue();
        // Setting null should not add the key
        $issue->setAssignedToId(null);

        $data = $issue->getData();
        self::assertArrayNotHasKey('assigned_to_id', $data);
    }

    public function testSetAssignedToIdWithNullDoesNotRemoveExistingKey(): void
    {
        $issue = new Issue();
        $issue->setAssignedToId(42);
        // Setting null after setting a value does not remove it (by design)
        $issue->setAssignedToId(null);

        $data = $issue->getData();
        // The key remains because setAssignedToId only adds if value is not null
        self::assertArrayHasKey('assigned_to_id', $data);
        self::assertSame(42, $data['assigned_to_id']);
    }
}
