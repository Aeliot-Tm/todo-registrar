<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Dto\InlineConfig;

use Aeliot\TodoRegistrar\Dto\InlineConfig\Token;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Token::class)]
final class TokenTest extends TestCase
{
    public function testGettersReturnCorrectValue(): void
    {
        $token = new Token('value_of_token', 1, 2);
        self::assertSame(2, $token->getPosition());
        self::assertSame(1, $token->getType());
        self::assertSame('value_of_token', $token->getValue());
    }
}
