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

namespace Aeliot\TodoRegistrar\Test\Unit\Dto;

use Aeliot\TodoRegistrar\Dto\FileHeap;
use Aeliot\TodoRegistrar\Dto\Parsing\CommentNode;
use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Service\File\FileParser;
use Aeliot\TodoRegistrar\Service\File\Saver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileHeap::class)]
#[UsesClass(FileParser::class)]
final class FileHeapTest extends TestCase
{
    public static function getDataForTestGluing(): iterable
    {
        yield 'single block without gluing' => [
            [
                '// TODO: with some summary',
                '//       - point one',
                '//       - point two',
            ],
            __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments.php',
            false,
        ];

        yield 'single block with gluing' => [
            [
                "// TODO: with some summary\n//       - point one\n//       - point two",
            ],
            __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments.php',
            \true,
        ];

        yield 'blank line between without gluing' => [
            [
                '// TODO: with some summary',
                '//       - point one',
                '//       - point two',
            ],
            __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments_split.php',
            false,
        ];

        yield 'blank line between with gluing' => [
            [
                "// TODO: with some summary\n//       - point one",
                '//       - point two',
            ],
            __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments_split.php',
            \true,
        ];

        yield 'trailing spaces without gluing' => [
            [
                '// TODO: with some summary  ',
                '//       - point one  ',
                '//       - point two',
            ],
            __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments_with_trailing_stace.php',
            false,
        ];

        yield 'trailing spaces with gluing' => [
            [
                "// TODO: with some summary  \n//       - point one  \n//       - point two",
            ],
            __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments_with_trailing_stace.php',
            \true,
        ];

        yield 'shifted block without gluing' => [
            [
                '// TODO: with some summary',
                '//       - point one',
                '//       - point two',
            ],
            __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments_shifted.php',
            false,
        ];

        yield 'shifted block with gluing' => [
            [
                "// TODO: with some summary\n        //       - point one\n            //       - point two",
            ],
            __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments_shifted.php',
            \true,
        ];
    }

    #[DataProvider('getDataForTestGluing')]
    public function testGluing(array $expectedTexts, string $pathname, bool $glueSequentialComments): void
    {
        self::assertSame($expectedTexts, array_map(
            static fn (CommentNode $x): string => $x->getToken()->getText(),
            $this->getCommentNodes($pathname, $glueSequentialComments),
        ));
    }

    /**
     * @return CommentNode[]
     */
    private function getCommentNodes(string $pathname, bool $glueSequentialComments): array
    {
        $parsedFile = (new FileParser())->parse($this->getMockSplFileInfo($pathname));
        $statistic = new ProcessStatistic();
        $saver = $this->createMock(Saver::class);
        $fileHeap = new FileHeap($parsedFile, $glueSequentialComments, $statistic, $saver);

        return $fileHeap->getCommentNodes();
    }

    private function getMockSplFileInfo(string $pathname): \SplFileInfo
    {
        $mock = $this->createMock(\SplFileInfo::class);
        $mock->method('getPathname')->willReturn($pathname);

        return $mock;
    }
}
