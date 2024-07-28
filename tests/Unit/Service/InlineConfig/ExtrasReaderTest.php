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
        yield 'simple value' => [
            ['key' => 'value'],
            '{EXTRAS:{key:value}}',
        ];

        yield 'indexed array with one element as value' => [
            ['key' => ['value']],
            '{EXTRAS:{key:[value]}}',
        ];

        yield 'indexed array with two elements as value' => [
            ['key' => ['value1', 'value2']],
            '{EXTRAS:{key:[value1, value2]}}',
        ];

        yield 'indexed array with two elements on second level' => [
            ['level_1' => ['level_2' => ['v1', 'v2']]],
            '{EXTRAS:{level_1:{level_2:[v1,v2]}}}',
        ];

        yield 'indexed array with two elements on third level' => [
            ['level_1' => ['level_2' => ['level_3' => ['v1', 'v2']]]],
            '{EXTRAS:{level_1:{level_2:{level_3:[v1,v2]}}}}',
        ];

        yield 'two keys with simple values' => [
            ['key1' => 'value1', 'key2' => 'value2'],
            '{EXTRAS:{key1:value1,key2:value2}}',
        ];

        yield 'some complex collection' => [
            ['level_1' => ['level_2' => ['level_3' => ['v1', 'v2'], 'level_3p2' => 'v3']]],
            '{EXTRAS:{level_1:{level_2:{level_3:[v1,v2],level_3p2: v3}}}}',
        ];

        yield 'some complex collection with multi-line formatting' => [
            ['level_1' => ['level_2' => ['level_3' => ['v1', 'v2'], 'level_3p2' => 'v3']]],
            <<<COMMENT
            {
                EXTRAS: {
                    level_1: {
                        level_2: {
                            level_3: [v1,v2],
                            level_3p2: v3,
                        }
                    }
                }
            }
            COMMENT,
        ];

        yield 'strange indents and spaces, but nevertheless it may be for some reason' => [
            [
                'level_1' => [
                    'level_2' => [
                        'level_3' => ['v1', 'v2'],
                        'level_3p2' => 'v3',
                    ],
                    'level_2p2' => 'v4',
                ],
                'level_1p2' => 'v5',
            ],
            <<<COMMENT
            {
                EXTRAS
                :
                 {
                    level_1
                    :
                     {
                        level_2
                        :
                         {
                            level_3
                            :
                            [
                                v1
                                ,
                                v2
                            ]
                            ,
                            level_3p2
                            :
                            v3,
                        }
                        ,
                            level_2p2
                            :
                            v4
                    }
                    ,
                        level_1p2
                        :
                        v5
                }
            }
            COMMENT,
        ];

        yield 'with trailing dot' => [
            ['some_key' => 'TD-123'],
            <<<COMMENT
            /**
              * TODO: some comment 
              *       with inline config {EXTRAS: {some_key: TD-123}}.
              */
            COMMENT,
        ];
    }

    #[DataProvider('getDataForTestPositiveFlow')]
    public function testPositiveFlow(array $expected, string $input): void
    {
        $actual = (new ExtrasReader(new ArrayFromJsonLikeLexerBuilder()))->getInlineConfig($input);
        self::assertSame($expected, $actual);
    }
}
