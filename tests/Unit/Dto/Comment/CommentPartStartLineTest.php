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

namespace Aeliot\TodoRegistrar\Test\Unit\Dto\Comment;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\FileHeap;
use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Service\Comment\Cleaner\PhpCommentCleaner;
use Aeliot\TodoRegistrar\Service\Comment\CommentCleanerRegistry;
use Aeliot\TodoRegistrar\Service\Comment\Extractor;
use Aeliot\TodoRegistrar\Service\File\FileParser;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentPart::class)]
#[UsesClass(CommentCleanerRegistry::class)]
#[UsesClass(Extractor::class)]
#[UsesClass(FileHeap::class)]
#[UsesClass(FileParser::class)]
#[UsesClass(PhpCommentCleaner::class)]
#[UsesClass(TagDetector::class)]
final class CommentPartStartLineTest extends TestCase
{
    /**
     * @return iterable<string, array{string, int[], bool}>
     */
    public static function getDataForTestStartLineMatchesExpected(): iterable
    {
        yield 'block comment with multiple TODOs' => [
            __DIR__ . '/../../../fixtures/start_line/block_with_multiple_todos.php',
            [8, 13],
            false,
        ];

        yield 'sequential single-line comments with gluing' => [
            __DIR__ . '/../../../fixtures/start_line/sequential_single_line_comments.php',
            [6, 9],
            true,
        ];
    }

    /**
     * @param int[] $expectedStartLines
     */
    #[DataProvider('getDataForTestStartLineMatchesExpected')]
    public function testStartLineMatchesExpected(string $pathname, array $expectedStartLines, bool $glueSequentialComments): void
    {
        $todos = $this->extractTodos($pathname, $glueSequentialComments);
        $actualStartLines = array_map(static fn (CommentPart $part): int => $part->getStartLine(), $todos);

        self::assertSame($expectedStartLines, $actualStartLines);
    }

    /**
     * @return CommentPart[]
     */
    private function extractTodos(string $pathname, bool $glueSequentialComments): array
    {
        $parsedFile = (new FileParser())->parse(new \SplFileInfo($pathname));
        $statistic = new ProcessStatistic();
        $saver = $this->createMock(Saver::class);
        $fileHeap = new FileHeap($parsedFile, $glueSequentialComments, $statistic, $saver);

        $extractor = new Extractor(
            new TagDetector(['todo', 'fixme'], [':', '-']),
            new CommentCleanerRegistry([new PhpCommentCleaner()]),
        );

        $allTodos = [];
        foreach ($fileHeap->getCommentNodes() as $commentNode) {
            $allTodos[] = $extractor->extract($commentNode);
        }

        return array_merge(...$allTodos);
    }
}
