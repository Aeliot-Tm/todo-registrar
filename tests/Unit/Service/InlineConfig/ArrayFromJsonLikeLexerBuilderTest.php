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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\InlineConfig;

use Aeliot\TodoRegistrar\Dto\InlineConfig\IndexedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\NamedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\Token;
use Aeliot\TodoRegistrar\Service\InlineConfig\ArrayFromJsonLikeLexerBuilder;
use Aeliot\TodoRegistrar\Service\InlineConfig\JsonLikeLexer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayFromJsonLikeLexerBuilder::class)]
#[UsesClass(JsonLikeLexer::class)]
#[UsesClass(Token::class)]
#[UsesClass(NamedCollection::class)]
#[UsesClass(IndexedCollection::class)]
final class ArrayFromJsonLikeLexerBuilderTest extends TestCase
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
        $actual = (new ArrayFromJsonLikeLexerBuilder())->build(new JsonLikeLexer($input));
        self::assertSame($expected, $actual);
    }
}
