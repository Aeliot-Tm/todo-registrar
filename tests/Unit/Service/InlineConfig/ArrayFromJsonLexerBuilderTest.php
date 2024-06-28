<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Service\InlineConfig;

use Aeliot\TodoRegistrar\Dto\InlineConfig\IndexedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\NamedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\Token;
use Aeliot\TodoRegistrar\Service\InlineConfig\ArrayFromJsonLexerBuilder;
use Aeliot\TodoRegistrar\Service\InlineConfig\JsonLexer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayFromJsonLexerBuilder::class)]
#[UsesClass(JsonLexer::class)]
#[UsesClass(Token::class)]
#[UsesClass(NamedCollection::class)]
#[UsesClass(IndexedCollection::class)]
final class ArrayFromJsonLexerBuilderTest extends TestCase
{
    /**
     * @return iterable<array{0: array<array-key,mixed>, 1: string}>
     */
    public static function getDataForTestPositiveFlow(): iterable
    {
        yield [['key' => 'value'], '{key: value}'];
        yield [['key' => ['value']], '{key: [value]}'];
    }

    #[DataProvider('getDataForTestPositiveFlow')]
    public function testPositiveFlow(array $expected, string $input): void
    {
        $actual = (new ArrayFromJsonLexerBuilder())->build(new JsonLexer($input));
        self::assertSame($expected, $actual);
    }
}