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

namespace Aeliot\TodoRegistrar\Service\Registrar\GitLab;

use Gitlab\Client;

/**
 * Resolves GitLab user identifiers (username/email) to user IDs.
 * Caches results in memory to avoid repeated API calls.
 *
 * @internal
 */
final class UserResolver
{
    /**
     * @var array<string,int> Cache mapping identifier => user ID
     */
    private array $cache = [];

    public function __construct(
        private Client $client,
    ) {
    }

    /**
     * Resolve a single user identifier to user ID.
     *
     * @param string $identifier Username or email
     */
    public function resolveUserId(string $identifier): ?int
    {
        if (isset($this->cache[$identifier])) {
            return $this->cache[$identifier];
        }

        $userId = $this->findUserId($identifier);
        if (null !== $userId) {
            $this->cache[$identifier] = $userId;
        }

        return $userId;
    }

    /**
     * Resolve multiple user identifiers to user IDs.
     *
     * @param string[] $identifiers Array of usernames or emails
     *
     * @return int[] Array of user IDs (only found users)
     */
    public function resolveUserIds(array $identifiers): array
    {
        $userIds = [];
        foreach ($identifiers as $identifier) {
            $userId = $this->resolveUserId($identifier);
            if (null !== $userId) {
                $userIds[] = $userId;
            }
        }

        return $userIds;
    }

    /**
     * @param array<string,mixed> $user
     */
    private function cacheUser(array $user, string $identifier): int
    {
        $userId = (int) $user['id'];
        // Cache the result
        $this->cache[$identifier] = $userId;
        // Also cache by username and email if available
        if (isset($user['username'])) {
            $this->cache[$user['username']] = $userId;
        }
        if (isset($user['email'])) {
            $this->cache[$user['email']] = $userId;
        }

        return $userId;
    }

    private function findUserId(string $identifier): ?int
    {
        // Try to search by username/email using search API
        try {
            $users = $this->client->users()->search($identifier);
            if (\is_array($users)) {
                foreach ($users as $user) {
                    // Match by exact username or email
                    if ($this->isSameUser($user, $identifier)) {
                        return $this->cacheUser($user, $identifier);
                    }
                }
            }
        } catch (\Throwable) {
            // If search fails, continue to fallback
        }

        // Fallback: get all users and search (with pagination support)
        try {
            $page = 1;
            $perPage = 100;
            do {
                $users = $this->client->users()->all(['page' => $page, 'per_page' => $perPage]);
                if (!\is_array($users) || empty($users)) {
                    break;
                }

                foreach ($users as $user) {
                    // Match by exact username or email
                    if ($this->isSameUser($user, $identifier)) {
                        return $this->cacheUser($user, $identifier);
                    }
                }

                // Continue to next page if we got full page of results
                ++$page;
            } while (\count($users) === $perPage);
        } catch (\Throwable) {
            // User not found or API error
        }

        return null;
    }

    /**
     * @param array<string,mixed> $user
     */
    private function isSameUser(array $user, string $identifier): bool
    {
        return (isset($user['username']) && $user['username'] === $identifier)
            || (isset($user['email']) && $user['email'] === $identifier);
    }
}
