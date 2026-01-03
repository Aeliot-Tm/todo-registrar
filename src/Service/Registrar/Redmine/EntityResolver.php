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
        private int|string $projectIdentifier,
    ) {
    }

    /**
     * Resolve project identifier to project ID.
     *
     * @param int|string $identifier Project ID, name, or identifier
     */
    public function resolveProjectId(int|string $identifier): ?int
    {
        // If already an integer, return as is
        if (\is_int($identifier)) {
            return $identifier;
        }

        if (isset($this->cache['project'][$identifier])) {
            return $this->cache['project'][$identifier];
        }

        // Load projects if not cached
        if (!isset($this->cache['project'])) {
            $this->cache['project'] = [];
            $page = 1;
            $limit = 100;
            do {
                $response = $this->client->getApi('project')->list(['limit' => $limit, 'offset' => ($page - 1) * $limit]);
                if (!\is_array($response) || !isset($response['projects']) || empty($response['projects'])) {
                    break;
                }

                $projects = $response['projects'];
                foreach ($projects as $project) {
                    if (!isset($project['id'])) {
                        continue;
                    }

                    $projectId = (int) $project['id'];
                    // Cache by ID
                    $this->cache['project'][$projectId] = $projectId;
                    // Cache by name if available
                    if (isset($project['name'])) {
                        $this->cache['project'][$project['name']] = $projectId;
                    }
                    // Cache by identifier if available
                    if (isset($project['identifier'])) {
                        $this->cache['project'][$project['identifier']] = $projectId;
                    }
                }

                // Continue to next page if we got full page of results
                ++$page;
            } while (\count($projects) === $limit);
        }

        return $this->cache['project'][$identifier] ?? null;
    }

    /**
     * Get project ID from project identifier.
     */
    private function getProjectId(): ?int
    {
        if (\is_int($this->projectIdentifier)) {
            return $this->projectIdentifier;
        }

        return $this->resolveProjectId($this->projectIdentifier);
    }

    /**
     * Resolve tracker identifier to tracker ID.
     *
     * @param int|string $identifier Tracker ID or name
     */
    public function resolveTrackerId(int|string $identifier): ?int
    {
        return $this->resolveEntityId('tracker', $identifier, function (): array {
            $response = $this->client->getApi('tracker')->list();
            if (!\is_array($response) || !isset($response['trackers'])) {
                return [];
            }

            $result = [];
            foreach ($response['trackers'] as $tracker) {
                if (isset($tracker['id'], $tracker['name'])) {
                    $result[(int) $tracker['id']] = $tracker['name'];
                }
            }

            return $result;
        });
    }

    /**
     * Resolve priority identifier to priority ID.
     *
     * @param int|string $identifier Priority ID or name
     */
    public function resolvePriorityId(int|string $identifier): ?int
    {
        return $this->resolveEntityId('priority', $identifier, function (): array {
            $response = $this->client->getApi('issue_priority')->list();
            if (!\is_array($response) || !isset($response['issue_priorities'])) {
                return [];
            }

            $result = [];
            foreach ($response['issue_priorities'] as $priority) {
                if (isset($priority['id'], $priority['name'])) {
                    $result[(int) $priority['id']] = $priority['name'];
                }
            }

            return $result;
        });
    }

    /**
     * Resolve category identifier to category ID.
     *
     * @param int|string $identifier Category ID or name
     */
    public function resolveCategoryId(int|string $identifier): ?int
    {
        $projectId = $this->getProjectId();
        if (null === $projectId) {
            throw new \RuntimeException(\sprintf('Project "%s" not found', $this->projectIdentifier));
        }

        return $this->resolveEntityId('category', $identifier, function () use ($projectId): array {
            try {
                $response = $this->client->getApi('issue_category')->listByProject($projectId);
                if (!\is_array($response) || !isset($response['issue_categories'])) {
                    return [];
                }
            } catch (\Throwable) {
                // 403 Forbidden or other errors - return empty list (project may have no categories or no access)
                return [];
            }

            $result = [];
            foreach ($response['issue_categories'] as $category) {
                if (isset($category['id'], $category['name'])) {
                    $result[(int) $category['id']] = $category['name'];
                }
            }

            return $result;
        });
    }

    /**
     * Resolve version identifier to version ID.
     *
     * @param int|string $identifier Version ID or name
     */
    public function resolveVersionId(int|string $identifier): ?int
    {
        $projectId = $this->getProjectId();
        if (null === $projectId) {
            throw new \RuntimeException(\sprintf('Project "%s" not found', $this->projectIdentifier));
        }

        return $this->resolveEntityId('version', $identifier, function () use ($projectId): array {
            try {
                $response = $this->client->getApi('version')->listByProject($projectId);
                if (!\is_array($response) || !isset($response['versions'])) {
                    return [];
                }
            } catch (\Throwable) {
                // 403 Forbidden or other errors - return empty list (project may have no versions or no access)
                return [];
            }

            $result = [];
            foreach ($response['versions'] as $version) {
                if (isset($version['id'], $version['name'])) {
                    $result[(int) $version['id']] = $version['name'];
                }
            }

            return $result;
        });
    }

    /**
     * @param callable(): array<int,string> $loadEntities
     */
    private function resolveEntityId(string $entityType, int|string $identifier, callable $loadEntities): ?int
    {
        // If already an integer, return as is
        if (\is_int($identifier)) {
            return $identifier;
        }

        if (isset($this->cache[$entityType][$identifier])) {
            return $this->cache[$entityType][$identifier];
        }

        // Load entities if not cached
        if (!isset($this->cache[$entityType])) {
            $entities = $loadEntities();
            $this->cache[$entityType] = [];
            // Cache by ID
            foreach ($entities as $id => $name) {
                $this->cache[$entityType][$id] = $id;
            }
            // Cache by name
            foreach ($entities as $id => $name) {
                $this->cache[$entityType][$name] = $id;
            }
        }

        return $this->cache[$entityType][$identifier] ?? null;
    }
}
