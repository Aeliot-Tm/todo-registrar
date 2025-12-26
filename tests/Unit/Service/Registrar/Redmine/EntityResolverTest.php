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

use Aeliot\TodoRegistrar\Service\Registrar\Redmine\EntityResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Api\IssuePriority;
use Redmine\Api\Project;
use Redmine\Api\Tracker;
use Redmine\Api\Version;
use Redmine\Client\Client;

#[CoversClass(EntityResolver::class)]
final class EntityResolverTest extends TestCase
{
    public function testResolveProjectIdWithInteger(): void
    {
        $client = $this->createMock(Client::class);

        $resolver = new EntityResolver($client, 1);

        self::assertSame(1, $resolver->resolveProjectId(1));
    }

    public function testResolveProjectIdByName(): void
    {
        $projects = [
            'projects' => [
                [
                    'id' => 1,
                    'name' => 'My Project',
                    'identifier' => 'my-project',
                ],
            ],
        ];

        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn($projects);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('project')->willReturn($projectApi);

        $resolver = new EntityResolver($client, 1);

        self::assertSame(1, $resolver->resolveProjectId('My Project'));
    }

    public function testResolveProjectIdByIdentifier(): void
    {
        $projects = [
            'projects' => [
                [
                    'id' => 1,
                    'name' => 'My Project',
                    'identifier' => 'my-project',
                ],
            ],
        ];

        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn($projects);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('project')->willReturn($projectApi);

        $resolver = new EntityResolver($client, 1);

        self::assertSame(1, $resolver->resolveProjectId('my-project'));
    }

    public function testResolveProjectIdReturnsNullWhenNotFound(): void
    {
        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn(['projects' => []]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('project')->willReturn($projectApi);

        $resolver = new EntityResolver($client, 1);

        self::assertNull($resolver->resolveProjectId('nonexistent'));
    }

    public function testResolveProjectIdCachesResults(): void
    {
        $projects = [
            'projects' => [
                [
                    'id' => 1,
                    'name' => 'My Project',
                    'identifier' => 'my-project',
                ],
            ],
        ];

        $projectApi = $this->createMock(Project::class);
        $projectApi->expects(self::once())->method('list')->willReturn($projects);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('project')->willReturn($projectApi);

        $resolver = new EntityResolver($client, 1);

        // First call should make API request
        self::assertSame(1, $resolver->resolveProjectId('My Project'));

        // Second call should use cache
        self::assertSame(1, $resolver->resolveProjectId('My Project'));
    }

    public function testResolveTrackerIdWithInteger(): void
    {
        $client = $this->createMock(Client::class);

        $resolver = new EntityResolver($client, 1);

        $trackers = [
            'trackers' => [
                [
                    'id' => 1,
                    'name' => 'Bug',
                ],
            ],
        ];

        $trackerApi = $this->createMock(Tracker::class);
        $trackerApi->method('list')->willReturn($trackers);

        $client->method('getApi')->with('tracker')->willReturn($trackerApi);

        self::assertSame(1, $resolver->resolveTrackerId(1));
    }

    public function testResolveTrackerIdByName(): void
    {
        $trackers = [
            'trackers' => [
                [
                    'id' => 1,
                    'name' => 'Bug',
                ],
                [
                    'id' => 2,
                    'name' => 'Task',
                ],
            ],
        ];

        $trackerApi = $this->createMock(Tracker::class);
        $trackerApi->method('list')->willReturn($trackers);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('tracker')->willReturn($trackerApi);

        $resolver = new EntityResolver($client, 1);

        self::assertSame(2, $resolver->resolveTrackerId('Task'));
    }

    public function testResolvePriorityIdWithInteger(): void
    {
        $priorities = [
            'issue_priorities' => [
                [
                    'id' => 1,
                    'name' => 'Low',
                ],
            ],
        ];

        $priorityApi = $this->createMock(IssuePriority::class);
        $priorityApi->method('list')->willReturn($priorities);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('issue_priority')->willReturn($priorityApi);

        $resolver = new EntityResolver($client, 1);

        self::assertSame(1, $resolver->resolvePriorityId(1));
    }

    public function testResolvePriorityIdByName(): void
    {
        $priorities = [
            'issue_priorities' => [
                [
                    'id' => 1,
                    'name' => 'Low',
                ],
                [
                    'id' => 2,
                    'name' => 'High',
                ],
            ],
        ];

        $priorityApi = $this->createMock(IssuePriority::class);
        $priorityApi->method('list')->willReturn($priorities);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('issue_priority')->willReturn($priorityApi);

        $resolver = new EntityResolver($client, 1);

        self::assertSame(2, $resolver->resolvePriorityId('High'));
    }

    public function testResolveCategoryIdWithInteger(): void
    {
        $categories = [
            'issue_categories' => [
                [
                    'id' => 1,
                    'name' => 'Backend',
                ],
            ],
        ];

        $categoryApi = $this->createMock(IssueCategory::class);
        $categoryApi->method('listByProject')->with(1)->willReturn($categories);

        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn([
            'projects' => [
                [
                    'id' => 1,
                    'name' => 'My Project',
                ],
            ],
        ]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')
            ->willReturnCallback(function (string $api) use ($categoryApi, $projectApi) {
                return match ($api) {
                    'project' => $projectApi,
                    'issue_category' => $categoryApi,
                    default => null,
                };
            });

        $resolver = new EntityResolver($client, 1);

        self::assertSame(1, $resolver->resolveCategoryId(1));
    }

    public function testResolveCategoryIdByName(): void
    {
        $categories = [
            'issue_categories' => [
                [
                    'id' => 1,
                    'name' => 'Backend',
                ],
                [
                    'id' => 2,
                    'name' => 'Frontend',
                ],
            ],
        ];

        $categoryApi = $this->createMock(IssueCategory::class);
        $categoryApi->method('listByProject')->with(1)->willReturn($categories);

        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn([
            'projects' => [
                [
                    'id' => 1,
                    'name' => 'My Project',
                ],
            ],
        ]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')
            ->willReturnCallback(function (string $api) use ($categoryApi, $projectApi) {
                return match ($api) {
                    'project' => $projectApi,
                    'issue_category' => $categoryApi,
                    default => null,
                };
            });

        $resolver = new EntityResolver($client, 1);

        self::assertSame(2, $resolver->resolveCategoryId('Frontend'));
    }

    public function testResolveCategoryIdThrowsExceptionWhenProjectNotFound(): void
    {
        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn(['projects' => []]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('project')->willReturn($projectApi);

        $resolver = new EntityResolver($client, 'nonexistent');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Project "nonexistent" not found');

        $resolver->resolveCategoryId(1);
    }

    public function testResolveVersionIdWithInteger(): void
    {
        $versions = [
            'versions' => [
                [
                    'id' => 1,
                    'name' => 'v1.0',
                ],
            ],
        ];

        $versionApi = $this->createMock(Version::class);
        $versionApi->method('listByProject')->with(1)->willReturn($versions);

        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn([
            'projects' => [
                [
                    'id' => 1,
                    'name' => 'My Project',
                ],
            ],
        ]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')
            ->willReturnCallback(function (string $api) use ($versionApi, $projectApi) {
                return match ($api) {
                    'project' => $projectApi,
                    'version' => $versionApi,
                    default => null,
                };
            });

        $resolver = new EntityResolver($client, 1);

        self::assertSame(1, $resolver->resolveVersionId(1));
    }

    public function testResolveVersionIdByName(): void
    {
        $versions = [
            'versions' => [
                [
                    'id' => 1,
                    'name' => 'v1.0',
                ],
                [
                    'id' => 2,
                    'name' => 'v2.0',
                ],
            ],
        ];

        $versionApi = $this->createMock(Version::class);
        $versionApi->method('listByProject')->with(1)->willReturn($versions);

        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn([
            'projects' => [
                [
                    'id' => 1,
                    'name' => 'My Project',
                ],
            ],
        ]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')
            ->willReturnCallback(function (string $api) use ($versionApi, $projectApi) {
                return match ($api) {
                    'project' => $projectApi,
                    'version' => $versionApi,
                    default => null,
                };
            });

        $resolver = new EntityResolver($client, 1);

        self::assertSame(2, $resolver->resolveVersionId('v2.0'));
    }

    public function testResolveVersionIdThrowsExceptionWhenProjectNotFound(): void
    {
        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn(['projects' => []]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('project')->willReturn($projectApi);

        $resolver = new EntityResolver($client, 'nonexistent');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Project "nonexistent" not found');

        $resolver->resolveVersionId(1);
    }

    public function testResolveCategoryIdHandlesApiException(): void
    {
        $categoryApi = $this->createMock(IssueCategory::class);
        $categoryApi->method('listByProject')->willThrowException(new \Exception('403 Forbidden'));

        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn([
            'projects' => [
                [
                    'id' => 1,
                    'name' => 'My Project',
                ],
            ],
        ]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')
            ->willReturnCallback(function (string $api) use ($categoryApi, $projectApi) {
                return match ($api) {
                    'project' => $projectApi,
                    'issue_category' => $categoryApi,
                    default => null,
                };
            });

        $resolver = new EntityResolver($client, 1);

        self::assertNull($resolver->resolveCategoryId('Backend'));
    }

    public function testResolveVersionIdHandlesApiException(): void
    {
        $versionApi = $this->createMock(Version::class);
        $versionApi->method('listByProject')->willThrowException(new \Exception('403 Forbidden'));

        $projectApi = $this->createMock(Project::class);
        $projectApi->method('list')->willReturn([
            'projects' => [
                [
                    'id' => 1,
                    'name' => 'My Project',
                ],
            ],
        ]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')
            ->willReturnCallback(function (string $api) use ($versionApi, $projectApi) {
                return match ($api) {
                    'project' => $projectApi,
                    'version' => $versionApi,
                    default => null,
                };
            });

        $resolver = new EntityResolver($client, 1);

        self::assertNull($resolver->resolveVersionId('v1.0'));
    }

    public function testResolveProjectIdPagination(): void
    {
        $firstPage = [
            'projects' => array_fill(0, 100, ['id' => 1, 'name' => 'Project']),
        ];
        $secondPage = [
            'projects' => [
                [
                    'id' => 123,
                    'name' => 'Target Project',
                    'identifier' => 'target-project',
                ],
            ],
        ];

        $projectApi = $this->createMock(Project::class);
        $projectApi->expects(self::exactly(2))
            ->method('list')
            ->willReturnOnConsecutiveCalls($firstPage, $secondPage);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('project')->willReturn($projectApi);

        $resolver = new EntityResolver($client, 1);

        self::assertSame(123, $resolver->resolveProjectId('Target Project'));
    }
}
