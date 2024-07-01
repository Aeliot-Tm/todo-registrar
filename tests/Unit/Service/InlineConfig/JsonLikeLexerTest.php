<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Service\InlineConfig;

use Aeliot\TodoRegistrar\Dto\InlineConfig\Token;
use Aeliot\TodoRegistrar\Service\InlineConfig\JsonLikeLexer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonLikeLexer::class)]
#[UsesClass(Token::class)]
final class JsonLikeLexerTest extends TestCase
{
    /**
     * @return iterable<array{0: array<int>, 1: string}>
     */
    public static function getDataForTestStructureMatch(): iterable
    {
        yield [
            [
                [
                    't' => JsonLikeLexer::T_CURLY_BRACES_OPEN,
                    'v' => '{',
                    'p' => 0,
                ],
                [
                    't' => JsonLikeLexer::T_KEY,
                    'v' => 'key',
                    'p' => 1,
                ],
                [
                    't' => JsonLikeLexer::T_COLON,
                    'v' => ':',
                    'p' => 4,
                ],
                [
                    't' => JsonLikeLexer::T_STRING,
                    'v' => 'value',
                    'p' => 6,
                ],
                [
                    't' => JsonLikeLexer::T_CURLY_BRACES_CLOSE,
                    'v' => '}',
                    'p' => 11,
                ],
            ],
            '{key: value}',
        ];

        yield [
            [
                [
                    't' => JsonLikeLexer::T_CURLY_BRACES_OPEN,
                    'v' => '{',
                    'p' => 0,
                ],
                [
                    't' => JsonLikeLexer::T_KEY,
                    'v' => 'key',
                    'p' => 1,
                ],
                [
                    't' => JsonLikeLexer::T_COLON,
                    'v' => ':',
                    'p' => 4,
                ],
                [
                    't' => JsonLikeLexer::T_SQUARE_BRACKET_OPEN,
                    'v' => '[',
                    'p' => 6,
                ],
                [
                    't' => JsonLikeLexer::T_STRING,
                    'v' => 'value',
                    'p' => 7,
                ],
                [
                    't' => JsonLikeLexer::T_SQUARE_BRACKET_CLOSE,
                    'v' => ']',
                    'p' => 12,
                ],
                [
                    't' => JsonLikeLexer::T_CURLY_BRACES_CLOSE,
                    'v' => '}',
                    'p' => 13,
                ],
            ],
            '{key: [value]}',
        ];

        yield [
            [
                [
                    't' => JsonLikeLexer::T_CURLY_BRACES_OPEN,
                    'v' => '{',
                    'p' => 0,
                ],
                [
                    't' => JsonLikeLexer::T_KEY,
                    'v' => 'key',
                    'p' => 3,
                ],
                [
                    't' => JsonLikeLexer::T_COLON,
                    'v' => ':',
                    'p' => 6,
                ],
                [
                    't' => JsonLikeLexer::T_SQUARE_BRACKET_OPEN,
                    'v' => '[',
                    'p' => 8,
                ],
                [
                    't' => JsonLikeLexer::T_STRING,
                    'v' => 'value',
                    'p' => 9,
                ],
                [
                    't' => JsonLikeLexer::T_SQUARE_BRACKET_CLOSE,
                    'v' => ']',
                    'p' => 14,
                ],
                [
                    't' => JsonLikeLexer::T_CURLY_BRACES_CLOSE,
                    'v' => '}',
                    'p' => 15,
                ],
            ],
            '{  key: [value]}',
        ];

        yield [
            [
                [
                    't' => JsonLikeLexer::T_CURLY_BRACES_OPEN,
                    'v' => '{',
                    'p' => 0,
                ],
                [
                    't' => JsonLikeLexer::T_KEY,
                    'v' => '_key',
                    'p' => 1,
                ],
                [
                    't' => JsonLikeLexer::T_COLON,
                    'v' => ':',
                    'p' => 5,
                ],
                [
                    't' => JsonLikeLexer::T_STRING,
                    'v' => 'value-parted',
                    'p' => 8,
                ],
                [
                    't' => JsonLikeLexer::T_CURLY_BRACES_CLOSE,
                    'v' => '}',
                    'p' => 21,
                ],
            ],
            '{_key:  value-parted }',
        ];
    }

    /**
     * @return iterable<array{0: int, 1: string}>
     */
    public static function getDataForTestSymbolMatch(): iterable
    {
        yield [JsonLikeLexer::T_COMMA, ','];
        yield [JsonLikeLexer::T_COLON, ':'];
        yield [JsonLikeLexer::T_SQUARE_BRACKET_OPEN, '['];
        yield [JsonLikeLexer::T_SQUARE_BRACKET_CLOSE, ']'];
        yield [JsonLikeLexer::T_CURLY_BRACES_OPEN, '{'];
        yield [JsonLikeLexer::T_CURLY_BRACES_CLOSE, '}'];
    }

    #[DataProvider('getDataForTestStructureMatch')]
    public function testStructureMatch(array $expectedTokenValues, string $input): void
    {
        $actualTokenValues = array_map(
            static fn (Token $token): array => [
                't' => $token->getType(),
                'v' => $token->getValue(),
                'p' => $token->getPosition(),
            ],
            iterator_to_array(new JsonLikeLexer($input)),
        );

        self::assertSame($expectedTokenValues, $actualTokenValues);
    }

    #[DataProvider('getDataForTestSymbolMatch')]
    public function testSymbolMatch(int $expectedType, string $input): void
    {
        $lexer = new JsonLikeLexer($input);
        self::assertCount(1, $lexer);

        $token = $lexer->current();
        self::assertNotNull($token);

        self::assertSame($expectedType, $token->getType());
    }
}
