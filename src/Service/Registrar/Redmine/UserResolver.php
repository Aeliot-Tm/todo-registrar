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
 * Resolves Redmine user identifiers (login/email) to user IDs.
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
        private readonly Client $client,
    ) {
    }

    /**
     * Resolve a single user identifier to user ID.
     *
     * @param string|int $identifier Login, email, or user ID
     */
    public function resolveUserId(string|int $identifier): ?int
    {
        // If already an integer, return as is
        if (\is_int($identifier)) {
            return $identifier;
        }

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
     * @param array<string|int> $identifiers Array of logins, emails, or user IDs
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
        // Also cache by login and email if available
        if (isset($user['login'])) {
            $this->cache[$user['login']] = $userId;
        }
        if (isset($user['mail'])) {
            $this->cache[$user['mail']] = $userId;
        }

        return $userId;
    }

    private function findUserId(string $identifier): ?int
    {
        // Try to get user by ID first (if identifier is numeric string)
        if (ctype_digit($identifier)) {
            try {
                $user = $this->client->getApi('user')->show((int) $identifier);
                if (\is_array($user) && isset($user['user'])) {
                    return $this->cacheUser($user['user'], $identifier);
                }
            } catch (\Throwable) {
                // If not found by ID, continue to search
            }
        }

        // Search through users by login or email
        try {
            $page = 1;
            $limit = 100;
            do {
                $response = $this->client->getApi('user')->list(['limit' => $limit, 'offset' => ($page - 1) * $limit]);
                if (!\is_array($response) || !isset($response['users']) || empty($response['users'])) {
                    break;
                }

                $users = $response['users'];
                foreach ($users as $user) {
                    // Match by exact login or email
                    if ($this->isSameUser($user, $identifier)) {
                        return $this->cacheUser($user, $identifier);
                    }
                }

                // Continue to next page if we got full page of results
                ++$page;
            } while (\count($users) === $limit);
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
        return (isset($user['login']) && $user['login'] === $identifier)
            || (isset($user['mail']) && $user['mail'] === $identifier);
    }
}
