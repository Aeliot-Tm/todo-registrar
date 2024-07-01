<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Service\InlineConfig;

use Aeliot\TodoRegistrar\Dto\InlineConfig\IndexedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\NamedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\Token;
use Aeliot\TodoRegistrar\Service\InlineConfig\ArrayFromJsonLikeLexerBuilder;
use Aeliot\TodoRegistrar\Service\InlineConfig\ExtrasReader;
use Aeliot\TodoRegistrar\Service\InlineConfig\JsonLikeLexer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtrasReader::class)]
#[UsesClass(ArrayFromJsonLikeLexerBuilder::class)]
#[UsesClass(IndexedCollection::class)]
#[UsesClass(JsonLikeLexer::class)]
#[UsesClass(NamedCollection::class)]
#[UsesClass(Token::class)]
final class ExtrasReaderTest extends TestCase
{
    /**
     * @return iterable<array{0: array<array-key,mixed>, 1: string}>
     */
    public static function getDataForTestPositiveFlow(): iterable
    {
        yield [['key' => ['value']], '{EXTRAS:{key:[value]}}'];
    }

    #[DataProvider('getDataForTestPositiveFlow')]
    public function testPositiveFlow(array $expected, string $input): void
    {
        $actual = (new ExtrasReader(new ArrayFromJsonLikeLexerBuilder()))->getInlineConfig($input);
        self::assertSame($expected, $actual);
    }
}
