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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Comment;

use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Aeliot\TodoRegistrar\Service\Comment\CommentCleanerInterface;
use Aeliot\TodoRegistrar\Service\Comment\CommentCleanerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentCleanerRegistry::class)]
final class CommentCleanerRegistryTest extends TestCase
{
    public function testReturnsFirstMatchingCleaner(): void
    {
        $firstCleaner = $this->createMock(CommentCleanerInterface::class);
        $firstCleaner->method('supports')->willReturn(true);

        $secondCleaner = $this->createMock(CommentCleanerInterface::class);
        $secondCleaner->method('supports')->willReturn(true);

        $registry = new CommentCleanerRegistry([$firstCleaner, $secondCleaner]);

        $token = $this->createMock(TokenInterface::class);
        self::assertSame($firstCleaner, $registry->getCleaner($token));
    }

    public function testThrowsExceptionWhenNoCleanerSupportsToken(): void
    {
        $cleaner = $this->createMock(CommentCleanerInterface::class);
        $cleaner->method('supports')->willReturn(false);

        $registry = new CommentCleanerRegistry([$cleaner]);

        $this->expectException(\RuntimeException::class);
        $registry->getCleaner($this->createMock(TokenInterface::class));
    }

    public function testThrowsExceptionWithEmptyCleaners(): void
    {
        $registry = new CommentCleanerRegistry([]);

        $this->expectException(\RuntimeException::class);
        $registry->getCleaner($this->createMock(TokenInterface::class));
    }
}
