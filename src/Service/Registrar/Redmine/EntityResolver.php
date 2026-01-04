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

namespace Aeliot\TodoRegistrar\Service\Registrar\Redmine;

use Redmine\Client\Client;

/**
 * Resolves Redmine entity identifiers (ID or name) to entity IDs.
 * Caches results in memory to avoid repeated API calls.
 *
 * @internal
 */
final class EntityResolver
{
    /**
     * @var array<string,array<int|string,int>> Cache mapping entity type => identifier => ID
     */
    private array $cache = [];

    public function __construct(
        private Client $client,
    ) {
    }

    /**
     * Resolve project identifier to project ID.
     *
     * @param int|string $identifier Project ID, name, or identifier
     */
    public function resolveProjectId(int|string $identifier): ?int
    {
        return $this->resolveEntityId('project', $identifier, function (string $entityType): void {
            $page = 1;
            $limit = 100;
            do {
                $response = $this->client->getApi('project')->list([
                    'limit' => $limit,
                    'offset' => ($page - 1) * $limit,
                ]);
                $items = $response['projects'] ?? [];
                $this->casheIdentifiers($entityType, $items, function (array $item) use ($entityType) {
                    $id = (int) $item['id'];
                    $this->cache[$entityType][$id] = $id;
                    if (isset($item['name'])) {
                        $this->cache[$entityType][$item['name']] = $id;
                    }
                    if (isset($item['identifier'])) {
                        $this->cache[$entityType][$item['identifier']] = $id;
                    }
                });
                ++$page;
                // Continue to next page if we got full page of results
            } while (\count($items) === $limit);
        });
    }

    /**
     * Resolve tracker identifier to tracker ID.
     *
     * @param int|string $identifier Tracker ID or name
     */
    public function resolveTrackerId(int|string $identifier): ?int
    {
        return $this->resolveEntityId('tracker', $identifier, function (string $entityType): void {
            $items = $this->client->getApi('tracker')->list()['trackers'] ?? [];
            $this->casheIdentifiers($entityType, $items);
        });
    }

    /**
     * @param int|string $identifier Priority ID or name
     */
    public function resolvePriorityId(int|string $identifier): ?int
    {
        return $this->resolveEntityId('priority', $identifier, function (string $entityType): void {
            $items = $this->client->getApi('issue_priority')->list()['issue_priorities'] ?? [];
            $this->casheIdentifiers($entityType, $items);
        });
    }

    /**
     * @param int|string $identifier Category ID or name
     */
    public function resolveCategoryId(int|string $identifier, int $projectId): ?int
    {
        return $this->resolveEntityId('category', $identifier, function (string $entityType) use ($projectId): void {
            try {
                $items = $this->client->getApi('issue_category')->listByProject($projectId)['issue_categories'] ?? [];
            } catch (\Throwable) {
                // 403 Forbidden or other errors - return empty list (project may have no categories or no access)
                $items = [];
            }

            $this->casheIdentifiers($entityType, $items);
        });
    }

    /**
     * @param int|string $identifier Version ID or name
     */
    public function resolveVersionId(int|string $identifier, int $projectId): ?int
    {
        return $this->resolveEntityId('version', $identifier, function (string $entityType) use ($projectId): void {
            try {
                $items = $this->client->getApi('version')->listByProject($projectId)['versions'] ?? [];
            } catch (\Throwable) {
                // 403 Forbidden or other errors - return empty list (project may have no versions or no access)
                $items = [];
            }

            $this->casheIdentifiers($entityType, $items);
        });
    }

    /**
     * @param array<array<string,int|string>> $items
     */
    private function casheIdentifiers(string $entityType, array $items, ?\Closure $casher = null): void
    {
        $casher ??= function (array $item) use ($entityType) {
            $id = (int) $item['id'];
            $this->cache[$entityType][$id] = $id;
            $this->cache[$entityType][$item['name']] = $id;
        };

        foreach ($items as $item) {
            $casher($item);
        }
    }

    /**
     * @param callable(string): void $loadEntities
     */
    private function resolveEntityId(string $entityType, int|string $identifier, callable $loadEntities): ?int
    {
        // If already an integer, return as is
        if (\is_int($identifier)) {
            return $identifier;
        }

        if (!isset($this->cache[$entityType])) {
            $this->cache[$entityType] = [];
            // Load entities if not cached
            $loadEntities($entityType);
        }

        return $this->cache[$entityType][$identifier] ?? null;
    }
}
