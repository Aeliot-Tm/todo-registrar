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
use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Aeliot\TodoRegistrar\Service\Comment\SequentialCommentGlueGate\YamlSequentialCommentGlueGate;
use Aeliot\TodoRegistrar\Service\File\Parser\YamlFileParser;
use Aeliot\TodoRegistrar\Service\File\Saver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * YAML sequential comment gluing with newline and indent tokens between # lines.
 */
#[CoversClass(FileHeap::class)]
#[UsesClass(YamlFileParser::class)]
final class FileHeapYamlGluingTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures/comments_gluing';

    /**
     * @return iterable<string, array{0: list<string>, 1: string, 2: bool}>
     */
    public static function provideGluingCases(): iterable
    {
        yield 'sequential comments without gluing' => [
            [
                '# TODO: title',
                '#       Description',
            ],
            self::FIXTURES_DIR . '/sequential_comments.yaml',
            false,
        ];

        yield 'sequential comments with gluing' => [
            [
                "# TODO: title\n    #       Description",
            ],
            self::FIXTURES_DIR . '/sequential_comments.yaml',
            true,
        ];

        yield 'single block without gluing' => [
            [
                '# TODO: with some summary',
                '#       - point one',
                '#       - point two',
            ],
            self::FIXTURES_DIR . '/block_of_single_comments.yaml',
            false,
        ];

        yield 'single block with gluing' => [
            [
                "# TODO: with some summary\n    #       - point one\n    #       - point two",
            ],
            self::FIXTURES_DIR . '/block_of_single_comments.yaml',
            true,
        ];

        yield 'blank line between without gluing' => [
            [
                '# TODO: with some summary',
                '#       - point one',
                '#       - point two',
            ],
            self::FIXTURES_DIR . '/block_of_single_comments_split.yaml',
            false,
        ];

        yield 'blank line between with gluing' => [
            [
                "# TODO: with some summary\n    #       - point one",
                '#       - point two',
            ],
            self::FIXTURES_DIR . '/block_of_single_comments_split.yaml',
            true,
        ];

        yield 'trailing spaces without gluing' => [
            [
                '# TODO: with some summary  ',
                '#       - point one  ',
                '#       - point two',
            ],
            self::FIXTURES_DIR . '/block_of_single_comments_with_trailing_stace.yaml',
            false,
        ];

        yield 'trailing spaces with gluing' => [
            [
                "# TODO: with some summary  \n    #       - point one  \n    #       - point two",
            ],
            self::FIXTURES_DIR . '/block_of_single_comments_with_trailing_stace.yaml',
            true,
        ];

        yield 'shifted block without gluing' => [
            [
                '# TODO: with some summary',
                '#       - point one',
                '#       - point two',
            ],
            self::FIXTURES_DIR . '/block_of_single_comments_shifted.yaml',
            false,
        ];

        yield 'shifted block with gluing' => [
            [
                "# TODO: with some summary\n        #       - point one\n            #       - point two",
            ],
            self::FIXTURES_DIR . '/block_of_single_comments_shifted.yaml',
            true,
        ];
    }

    #[DataProvider('provideGluingCases')]
    public function testSequentialYamlCommentsGluing(array $expectedTexts, string $filePath, bool $glueSequentialComments): void
    {
        self::assertFileExists($filePath);
        self::assertSame($expectedTexts, $this->getCommentNodeTexts($filePath, $glueSequentialComments));
    }

    public function testYamlTokenStreamAroundBlankLineBetweenComments(): void
    {
        $parsedFile = (new YamlFileParser())->parse($this->getMockSplFileInfo(self::FIXTURES_DIR . '/block_of_single_comments_split.yaml'));
        $stream = $parsedFile->getTokenStream();

        $snippet = [];
        $inBlock = false;

        while (!$stream->isEnd()) {
            $token = $stream->current();
            if (null === $token) {
                $stream->next();
                continue;
            }

            if ($token->isComment() && str_contains($token->getText(), 'point one')) {
                $inBlock = true;
            }

            if ($inBlock) {
                $snippet[] = $this->formatTokenForAssertion($token);

                if ($token->isComment() && str_contains($token->getText(), 'point two')) {
                    break;
                }
            }

            $stream->next();
        }

        self::assertSame(
            [
                ['line' => 3, 'kind' => 'comment', 'text' => '#       - point one', 'trimEmpty' => false],
                ['line' => 3, 'kind' => 'newline', 'text' => "\n", 'trimEmpty' => true],
                ['line' => 4, 'kind' => 'newline', 'text' => "\n", 'trimEmpty' => true],
                ['line' => 5, 'kind' => 'indent', 'text' => '    ', 'trimEmpty' => true],
                ['line' => 5, 'kind' => 'comment', 'text' => '#       - point two', 'trimEmpty' => false],
            ],
            $snippet,
            'Blank line between YAML comments is two newline tokens without glueable comment between them',
        );
    }

    public function testYamlTokenStreamBetweenConsecutiveComments(): void
    {
        $parsedFile = (new YamlFileParser())->parse($this->getMockSplFileInfo(self::FIXTURES_DIR . '/sequential_comments.yaml'));
        $stream = $parsedFile->getTokenStream();

        $snippet = [];
        $inBlock = false;

        while (!$stream->isEnd()) {
            $token = $stream->current();
            if (null === $token) {
                $stream->next();
                continue;
            }
            if ($token->isComment() && str_contains($token->getText(), 'TODO: title')) {
                $inBlock = true;
            }

            if ($inBlock) {
                $snippet[] = $this->formatTokenForAssertion($token);

                if ($token->isComment() && str_contains($token->getText(), 'Description')) {
                    break;
                }
            }

            $stream->next();
        }

        self::assertSame(
            [
                ['line' => 2, 'kind' => 'comment', 'text' => '# TODO: title', 'trimEmpty' => false],
                ['line' => 2, 'kind' => 'newline', 'text' => "\n", 'trimEmpty' => true],
                ['line' => 3, 'kind' => 'indent', 'text' => '    ', 'trimEmpty' => true],
                ['line' => 3, 'kind' => 'comment', 'text' => '#       Description', 'trimEmpty' => false],
            ],
            $snippet,
            'YAML emits newline and indent as separate tokens between consecutive comment lines',
        );
    }

    /**
     * @return array{line: int, kind: string, text: string, trimEmpty: bool}
     */
    private function formatTokenForAssertion(TokenInterface $token): array
    {
        $text = $token->getText();
        $kind = match (true) {
            $token->isComment() => 'comment',
            "\n" === $text || "\r\n" === $text => 'newline',
            '' === trim($text) && str_contains($text, ' ') => 'indent',
            default => 'other',
        };

        return [
            'line' => $token->getLine(),
            'kind' => $kind,
            'text' => $text,
            'trimEmpty' => '' === trim($text),
        ];
    }

    /**
     * @return CommentNode[]
     */
    private function getCommentNodes(string $pathname, bool $glueSequentialComments): array
    {
        $parsedFile = (new YamlFileParser())->parse($this->getMockSplFileInfo($pathname));
        $statistic = new ProcessStatistic();
        $saver = $this->createMock(Saver::class);
        $glueGate = $glueSequentialComments ? new YamlSequentialCommentGlueGate() : null;
        $fileHeap = new FileHeap($parsedFile, $glueSequentialComments, $glueGate, $statistic, $saver);

        return $fileHeap->getCommentNodes();
    }

    /**
     * @return list<string>
     */
    private function getCommentNodeTexts(string $pathname, bool $glueSequentialComments): array
    {
        return array_map(
            static fn (CommentNode $node): string => implode('', array_map(
                static fn (TokenInterface $token): string => $token->getText(),
                $node->getTokens(),
            )),
            $this->getCommentNodes($pathname, $glueSequentialComments),
        );
    }

    private function getMockSplFileInfo(string $pathname): \SplFileInfo
    {
        $mock = $this->createMock(\SplFileInfo::class);
        $mock->method('getPathname')->willReturn($pathname);

        return $mock;
    }
}
