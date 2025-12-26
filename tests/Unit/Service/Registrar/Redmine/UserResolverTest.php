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

use Aeliot\TodoRegistrar\Service\Registrar\Redmine\UserResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\User;
use Redmine\Client\Client;

#[CoversClass(UserResolver::class)]
final class UserResolverTest extends TestCase
{
    public function testResolveUserIdWithInteger(): void
    {
        $client = $this->createMock(Client::class);

        $resolver = new UserResolver($client);

        self::assertSame(123, $resolver->resolveUserId(123));
    }

    public function testResolveUserIdByNumericStringId(): void
    {
        $user = [
            'user' => [
                'id' => 123,
                'login' => 'testuser',
                'mail' => 'test@example.com',
            ],
        ];

        $userApi = $this->createMock(User::class);
        $userApi->method('show')->with(123)->willReturn($user);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('user')->willReturn($userApi);

        $resolver = new UserResolver($client);

        self::assertSame(123, $resolver->resolveUserId('123'));
    }

    public function testResolveUserIdByLogin(): void
    {
        $users = [
            'users' => [
                [
                    'id' => 123,
                    'login' => 'testuser',
                    'mail' => 'test@example.com',
                ],
            ],
        ];

        $userApi = $this->createMock(User::class);
        $userApi->method('list')->willReturn($users);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('user')->willReturn($userApi);

        $resolver = new UserResolver($client);

        self::assertSame(123, $resolver->resolveUserId('testuser'));
    }

    public function testResolveUserIdByEmail(): void
    {
        $users = [
            'users' => [
                [
                    'id' => 123,
                    'login' => 'testuser',
                    'mail' => 'test@example.com',
                ],
            ],
        ];

        $userApi = $this->createMock(User::class);
        $userApi->method('list')->willReturn($users);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('user')->willReturn($userApi);

        $resolver = new UserResolver($client);

        self::assertSame(123, $resolver->resolveUserId('test@example.com'));
    }

    public function testResolveUserIdReturnsNullWhenNotFound(): void
    {
        $userApi = $this->createMock(User::class);
        $userApi->method('list')->willReturn(['users' => []]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('user')->willReturn($userApi);

        $resolver = new UserResolver($client);

        self::assertNull($resolver->resolveUserId('nonexistent'));
    }

    public function testResolveUserIdCachesResults(): void
    {
        $users = [
            'users' => [
                [
                    'id' => 123,
                    'login' => 'testuser',
                    'mail' => 'test@example.com',
                ],
            ],
        ];

        $userApi = $this->createMock(User::class);
        $userApi->expects(self::once())->method('list')->willReturn($users);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('user')->willReturn($userApi);

        $resolver = new UserResolver($client);

        // First call should make API request
        self::assertSame(123, $resolver->resolveUserId('testuser'));

        // Second call should use cache
        self::assertSame(123, $resolver->resolveUserId('testuser'));
    }

    public function testResolveUserIdCachesByLoginAndEmail(): void
    {
        $users = [
            'users' => [
                [
                    'id' => 123,
                    'login' => 'testuser',
                    'mail' => 'test@example.com',
                ],
            ],
        ];

        $userApi = $this->createMock(User::class);
        $userApi->expects(self::once())->method('list')->willReturn($users);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('user')->willReturn($userApi);

        $resolver = new UserResolver($client);

        // Resolve by login
        self::assertSame(123, $resolver->resolveUserId('testuser'));

        // Resolve by email should use cache
        self::assertSame(123, $resolver->resolveUserId('test@example.com'));
    }

    public function testResolveUserIds(): void
    {
        $users = [
            'users' => [
                [
                    'id' => 123,
                    'login' => 'user1',
                ],
                [
                    'id' => 456,
                    'login' => 'user2',
                ],
            ],
        ];

        $userApi = $this->createMock(User::class);
        $userApi->method('list')->willReturn($users);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('user')->willReturn($userApi);

        $resolver = new UserResolver($client);

        $result = $resolver->resolveUserIds(['user1', 'user2', 'nonexistent']);

        self::assertSame([123, 456], $result);
    }

    public function testResolveUserIdHandlesApiException(): void
    {
        $userApi = $this->createMock(User::class);
        $userApi->method('show')->willThrowException(new \Exception('User not found'));
        $userApi->method('list')->willReturn(['users' => []]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('user')->willReturn($userApi);

        $resolver = new UserResolver($client);

        self::assertNull($resolver->resolveUserId('999'));
    }

    public function testResolveUserIdPagination(): void
    {
        $firstPage = [
            'users' => array_fill(0, 100, ['id' => 1, 'login' => 'user1']),
        ];
        $secondPage = [
            'users' => [
                [
                    'id' => 123,
                    'login' => 'targetuser',
                ],
            ],
        ];

        $userApi = $this->createMock(User::class);
        $userApi->expects(self::exactly(2))
            ->method('list')
            ->willReturnOnConsecutiveCalls($firstPage, $secondPage);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('user')->willReturn($userApi);

        $resolver = new UserResolver($client);

        self::assertSame(123, $resolver->resolveUserId('targetuser'));
    }

    public function testResolveUserIdFallsBackToSearchWhenIdNotFound(): void
    {
        $userApi = $this->createMock(User::class);
        $userApi->method('show')->with(999)->willThrowException(new \Exception('User not found'));
        // When show() fails, it falls back to list() search
        // The identifier '999' should match login '999'
        $userApi->method('list')->willReturn([
            'users' => [
                [
                    'id' => 999,
                    'login' => '999',
                ],
            ],
        ]);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('user')->willReturn($userApi);

        $resolver = new UserResolver($client);

        self::assertSame(999, $resolver->resolveUserId('999'));
    }
}
