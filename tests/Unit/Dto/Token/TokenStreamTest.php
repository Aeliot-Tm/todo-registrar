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

namespace Aeliot\TodoRegistrar\Test\Unit\Dto\Token;

use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Aeliot\TodoRegistrar\Dto\Token\TokenStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TokenStream::class)]
final class TokenStreamTest extends TestCase
{
    public function testTraversalAndPeek(): void
    {
        $first = $this->createMock(TokenInterface::class);
        $first->method('getText')->willReturn('a');
        $second = $this->createMock(TokenInterface::class);
        $second->method('getText')->willReturn('b');

        $tokens = [$first, $second];
        $stream = new TokenStream($tokens);

        self::assertFalse($stream->isEnd());
        self::assertSame($first, $stream->current());

        self::assertSame($second, $stream->peek(1));
        $stream->next();
        self::assertSame($second, $stream->current());

        self::assertNull($stream->peek(1));
        $stream->next();
        self::assertNull($stream->current());

        self::assertTrue($stream->isEnd());
        self::assertNull($stream->current());
        self::assertSame($tokens, $stream->getTokens());
    }
}
