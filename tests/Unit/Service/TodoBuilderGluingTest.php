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

namespace Aeliot\TodoRegistrar\Test\Unit\Service;

use Aeliot\TodoRegistrar\Console\OutputAdapter;
use Aeliot\TodoRegistrar\Dto\FileHeap;
use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\Enum\IssueKeyPosition;
use Aeliot\TodoRegistrar\Service\Comment\Extractor;
use Aeliot\TodoRegistrar\Service\File\FileParser;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\InlineConfig\ArrayFromJsonLikeLexerBuilder;
use Aeliot\TodoRegistrar\Service\InlineConfig\ExtrasReader;
use Aeliot\TodoRegistrar\Service\InlineConfig\InlineConfigFactory;
use Aeliot\TodoRegistrar\Service\Tag\Detector;
use Aeliot\TodoRegistrar\Service\TodoBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Tests demonstrating how Todo::$summary and Todo::$description are extracted
 * from glued sequential single-line comments.
 */
#[CoversClass(TodoBuilder::class)]
final class TodoBuilderGluingTest extends TestCase
{
    public function testSummaryAndDescriptionExtractionFromBasicBlockWithGluing(): void
    {
        $pathname = __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments.php';
        $todos = $this->extractTodosFromFile($pathname);

        self::assertCount(1, $todos, 'Expected 1 TODO when gluing is enabled');
        $todo = $todos[0];

        self::assertSame('with some summary', $todo->getSummary());
        self::assertSame(" - point one\n - point two", $todo->getDescription());
    }

    public function testSummaryAndDescriptionExtractionFromShiftedBlockWithGluing(): void
    {
        $pathname = __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments_shifted.php';
        $todos = $this->extractTodosFromFile($pathname);

        self::assertCount(1, $todos, 'Expected 1 TODO when gluing is enabled');
        $todo = $todos[0];

        self::assertSame('with some summary', $todo->getSummary());
        self::assertSame(" - point one\n - point two", $todo->getDescription());
    }

    public function testSummaryAndDescriptionExtractionFromReverseShiftedBlockWithGluing(): void
    {
        $pathname = __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments_shifted_reverse.php';
        $todos = $this->extractTodosFromFile($pathname);

        self::assertCount(1, $todos, 'Expected 1 TODO when gluing is enabled');
        $todo = $todos[0];

        self::assertSame('with some summary', $todo->getSummary());
        self::assertSame(" - point one\n - point two", $todo->getDescription());
    }

    public function testSummaryAndDescriptionExtractionFromMultiLineComment(): void
    {
        $pathname = __DIR__ . '/../../fixtures/comments_gluing/multiline_comment.php';
        $todos = $this->extractTodosFromFile($pathname);

        self::assertCount(1, $todos, 'Expected 1 TODO in multi-line comment');
        $todo = $todos[0];

        self::assertSame('with some summary', $todo->getSummary());
        self::assertSame(" - point one\n - point two\n", $todo->getDescription());
    }

    public function testSummaryAndDescriptionExtractionFromMultiLineCommentWithoutAsterisk(): void
    {
        $pathname = __DIR__ . '/../../fixtures/comments_gluing/multiline_comment_without_asterisk.php';
        $todos = $this->extractTodosFromFile($pathname);

        self::assertCount(1, $todos, 'Expected 1 TODO in multi-line comment without asterisk');
        $todo = $todos[0];

        self::assertSame('with some summary', $todo->getSummary());
        self::assertSame(" - point one\n - point two\n", $todo->getDescription());
    }

    public function testSummaryAndDescriptionExtractionFromMultiLineCommentWithBrokenIndentsWithoutAsterisk(): void
    {
        $pathname = __DIR__ . '/../../fixtures/comments_gluing/multiline_comment_without_asterisk_with_broken_indents.php';
        $todos = $this->extractTodosFromFile($pathname);

        self::assertCount(1, $todos, 'Expected 1 TODO in multi-line comment with broken indents without asterisk');
        $todo = $todos[0];

        self::assertSame('with some summary', $todo->getSummary());
        self::assertSame(" - point one\n - point two\n", $todo->getDescription());
    }

    public function testSummaryAndDescriptionExtractionFromMultiLineCommentWithBrokenIndents(): void
    {
        $pathname = __DIR__ . '/../../fixtures/comments_gluing/multiline_comment_with_broken_indents.php';
        $todos = $this->extractTodosFromFile($pathname);

        self::assertCount(1, $todos, 'Expected 1 TODO in multi-line comment with broken indents');
        $todo = $todos[0];

        self::assertSame('with some summary', $todo->getSummary());
        self::assertSame(" - point one\n - point two\n", $todo->getDescription());
    }

    public function testDiagnosticsForShiftedBlockGluing(): void
    {
        $pathname = __DIR__ . '/../../fixtures/comments_gluing/block_of_single_comments_shifted.php';
        $parsedFile = (new FileParser())->parse($this->getMockSplFileInfo($pathname));
        $statistic = new ProcessStatistic();
        $saver = $this->createMock(Saver::class);
        $fileHeap = new FileHeap($parsedFile, true, $statistic, $saver);

        $commentNodes = $fileHeap->getCommentNodes();
        self::assertCount(1, $commentNodes, 'Expected 1 glued comment node');

        $gluedToken = $commentNodes[0]->getToken();
        $gluedText = $gluedToken->getText();

        $expectedGluedText = "// TODO: with some summary\n        //       - point one\n            //       - point two";
        self::assertSame(
            $expectedGluedText,
            $gluedText,
            'Glued comment text should preserve original line structure with indentation'
        );

        $detector = new Detector(['TODO'], [':', '-', '>']);
        $extractor = new Extractor($detector);
        $commentParts = $extractor->extract($gluedToken, $commentNodes[0]->getContext());
        $todos = $commentParts->getTodos();

        self::assertCount(1, $todos, 'Extractor should find 1 TODO in glued comment');

        $commentPart = $todos[0];
        $lines = $commentPart->getLines();

        self::assertCount(3, $lines, 'CommentPart should contain all 3 lines from the glued comment');

        $todo = $this->extractTodosFromFile($pathname)[0];
        self::assertSame(
            " - point one\n - point two",
            $todo->getDescription(),
            'Description should normalize indentation relative to base indent after comment marker'
        );
    }

    /**
     * @return Todo[]
     */
    private function extractTodosFromFile(string $pathname): array
    {
        $parsedFile = (new FileParser())->parse($this->getMockSplFileInfo($pathname));
        $statistic = new ProcessStatistic();
        $saver = $this->createMock(Saver::class);
        $fileHeap = new FileHeap($parsedFile, true, $statistic, $saver);

        $detector = new Detector(['TODO'], [':', '-', '>']);
        $extractor = new Extractor($detector);
        $todoBuilder = new TodoBuilder(
            new InlineConfigFactory(),
            new ExtrasReader(new ArrayFromJsonLikeLexerBuilder()),
            IssueKeyPosition::AFTER_SEPARATOR,
            null,
            new OutputAdapter(new NullOutput()),
            false,
        );

        $todos = [];
        foreach ($fileHeap->getCommentNodes() as $commentNode) {
            $token = $commentNode->getToken();
            $context = $commentNode->getContext();
            $commentParts = $extractor->extract($token, $context);
            foreach ($commentParts->getTodos() as $commentPart) {
                $todos[] = $todoBuilder->create($commentPart);
            }
        }

        return $todos;
    }

    private function getMockSplFileInfo(string $pathname): \SplFileInfo
    {
        $mock = $this->createMock(\SplFileInfo::class);
        $mock->method('getPathname')->willReturn($pathname);

        return $mock;
    }
}
