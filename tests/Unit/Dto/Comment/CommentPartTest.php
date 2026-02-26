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
use Aeliot\TodoRegistrar\Dto\Parsing\MappedContext;
use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Dto\Token\TokenLine;
use Aeliot\TodoRegistrar\Enum\IssueKeyPosition;
use Aeliot\TodoRegistrar\Exception\NoLineException;
use Aeliot\TodoRegistrar\Exception\NoPrefixException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentPart::class)]
#[UsesClass(TagMetadata::class)]
#[UsesClass(IssueKeyPosition::class)]
#[UsesClass(TokenLine::class)]
final class CommentPartTest extends TestCase
{
    /**
     * @return iterable<array{0: string, 1: array<TokenLine>}>
     */
    public static function getDataForTestGetContent(): iterable
    {
        yield [
            'abc',
            [
                new TokenLine('', 'a', '', ''),
                new TokenLine('', 'b', '', ''),
                new TokenLine('', 'c', '', ''),
            ],
        ];

        yield [
            " * TODO: first line of description\n *       second line of description\n",
            [
                new TokenLine(' * ', 'TODO: first line of description', '', "\n"),
                new TokenLine(' * ', '      second line of description', '', "\n"),
            ],
        ];
    }

    /**
     * @return iterable<array{0: string, 1: array<TokenLine>, 2: int}>
     */
    public static function getDataForTestGetDescription(): iterable
    {
        yield [
            " second line of description\n" .
            " third line of description\n",
            [
                new TokenLine(' * ', 'TODO: first line of description', '', "\n"),
                new TokenLine(' * ', '      second line of description', '', "\n"),
                new TokenLine(' * ', '      third line of description', '', "\n"),
            ],
            5,
        ];

        yield [
            '',
            [
                new TokenLine(' # ', 'TODO: one line of description', '', ''),
            ],
            5,
        ];
    }

    /**
     * @return iterable<array{0: string, 1: array<TokenLine>, 2: int}>
     */
    public static function getDataForTestGetSummary(): iterable
    {
        yield [
            'first line of description',
            [
                new TokenLine(' * ', 'TODO: first line of description', '', "\n"),
                new TokenLine(' * ', '      second line of description', '', "\n"),
                new TokenLine(' * ', '      third line of description', '', "\n"),
            ],
            5,
        ];

        yield [
            'one line of description',
            [
                new TokenLine(' # ', 'TODO: one line of description', '', ''),
            ],
            5,
        ];
    }

    /**
     * @return iterable<array{
     *     0: string,
     *     1: string,
     *     2: IssueKeyPosition,
     *     3: int,
     *     4: int|null,
     *     5: array<TokenLine>,
     *     6: string|null,
     *     7: bool
     * }>
     */
    public static function getDataForTestInjectKey(): iterable
    {
        // Without separator (separatorOffset = null)
        yield [
            'TODO KEY-123 description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            null,
            [new TokenLine('', 'TODO description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO KEY-123 description',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            null,
            [new TokenLine('', 'TODO description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO KEY-123 description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            null,
            [new TokenLine('', 'TODO  description', '', '')],
            null,
            false,
        ];

        // BEFORE_SEPARATOR
        yield [
            'TODO KEY-123 : description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO: description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO KEY-123 : description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            5,
            [new TokenLine('', 'TODO : description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO KEY-123 : description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            6,
            [new TokenLine('', 'TODO  : description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO KEY-123  : description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            7,
            [new TokenLine('', 'TODO   : description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO KEY-123 - description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO- description', '', '')],
            null,
            false,
        ];

        // AFTER_SEPARATOR
        yield [
            'TODO: KEY-123 description',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO: description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO: KEY-123 description',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO:  description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO : KEY-123 description',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            5,
            [new TokenLine('', 'TODO : description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO : KEY-123 description',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            5,
            [new TokenLine('', 'TODO :  description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO : KEY-123  description',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            5,
            [new TokenLine('', 'TODO :   description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO- KEY-123 description',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO- description', '', '')],
            null,
            false,
        ];

        // Multi-line comments with AFTER_SEPARATOR
        yield [
            " * TODO: KEY-123 first line of description\n *       second line of description\n",
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            5,
            4,
            [
                new TokenLine(' * ', 'TODO: first line of description', '', "\n"),
                new TokenLine(' * ', '      second line of description', '', "\n"),
            ],
            null,
            false,
        ];

        // Multi-line comments with BEFORE_SEPARATOR
        yield [
            " * TODO KEY-123 : first line of description\n *       second line of description\n",
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            5,
            4,
            [
                new TokenLine(' * ', 'TODO: first line of description', '', "\n"),
                new TokenLine(' * ', '      second line of description', '', "\n"),
            ],
            null,
            false,
        ];

        // Edge cases: no spaces
        yield [
            'TODO KEY-123 :description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO:description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO: KEY-123 description',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO:description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO KEY-123 description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            null,
            [new TokenLine('', 'TODOdescription', '', '')],
            null,
            false,
        ];

        // BEFORE_SEPARATOR_STICKY
        yield [
            'TODO KEY-123: description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR_STICKY,
            4,
            4,
            [new TokenLine('', 'TODO: description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO KEY-123: description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR_STICKY,
            4,
            5,
            [new TokenLine('', 'TODO : description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO  KEY-123: description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR_STICKY,
            4,
            6,
            [new TokenLine('', 'TODO  : description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO    KEY-123: description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR_STICKY,
            4,
            8,
            [new TokenLine('', 'TODO    : description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO KEY-123- description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR_STICKY,
            4,
            4,
            [new TokenLine('', 'TODO- description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO   KEY-123- description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR_STICKY,
            4,
            7,
            [new TokenLine('', 'TODO   - description', '', '')],
            null,
            false,
        ];
        yield [
            'TODO KEY-123: description',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR_STICKY,
            4,
            4,
            [new TokenLine('', 'TODO: description', '', '')],
            null,
            false,
        ];
        yield [
            " * TODO KEY-123: first line of description\n *       second line of description\n",
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR_STICKY,
            5,
            4,
            [
                new TokenLine(' * ', 'TODO: first line of description', '', "\n"),
                new TokenLine(' * ', '      second line of description', '', "\n"),
            ],
            null,
            false,
        ];
        yield [
            " * TODO    KEY-123: first line of description\n *       second line of description\n",
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR_STICKY,
            5,
            8,
            [
                new TokenLine(' * ', 'TODO    : first line of description', '', "\n"),
                new TokenLine(' * ', '      second line of description', '', "\n"),
            ],
            null,
            false,
        ];

        // NewSeparator tests
        yield [
            'TODO: KEY-123 text',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO: text', '', '')],
            null,
            true,
        ];
        yield [
            'TODO: KEY-123 text',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO: text', '', '')],
            null,
            false,
        ];

        // Case 2: ReplaceSeparator=true and separator is found
        yield [
            'TODO- KEY-123 text',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO: text', '', '')],
            '-',
            true,
        ];
        yield [
            'TODO | KEY-123 text',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            5,
            [new TokenLine('', 'TODO : text', '', '')],
            '|',
            true,
        ];

        // Case 3: ReplaceSeparator=false and separator is found (ignore NewSeparator)
        yield [
            'TODO: KEY-123 text',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO: text', '', '')],
            '-',
            false,
        ];
        yield [
            'TODO- KEY-123 text',
            'KEY-123',
            IssueKeyPosition::AFTER_SEPARATOR,
            4,
            4,
            [new TokenLine('', 'TODO- text', '', '')],
            ':',
            false,
        ];

        // Case 4: Separator not found and NewSeparator is defined
        yield [
            'TODO KEY-123 - text',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            null,
            [new TokenLine('', 'TODO text', '', '')],
            '-',
            false,
        ];
        yield [
            'TODO KEY-123 - text',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            null,
            [new TokenLine('', 'TODO text', '', '')],
            '-',
            true,
        ];
        yield [
            'TODO KEY-123 : text',
            'KEY-123',
            IssueKeyPosition::BEFORE_SEPARATOR,
            4,
            null,
            [new TokenLine('', 'TODO  text', '', '')],
            ':',
            false,
        ];
    }

    /**
     * @return iterable<array<int|null>>
     */
    public static function getDataForTestInjectKeyThrowsExceptionWithoutPrefix(): iterable
    {
        yield [null];
        yield [0];
        yield [-1];
    }

    /**
     * @param TokenLine[] $lines
     */
    #[DataProvider('getDataForTestGetContent')]
    public function testGetContent(string $expectedContent, array $lines): void
    {
        $commentPart = $this->createCommentPartWithLines($lines);
        self::assertEquals($expectedContent, $commentPart->getContent());
    }

    public function testGetContentThrowsExceptionWithoutLines(): void
    {
        $this->expectException(NoLineException::class);
        $commentPart = new CommentPart(1, null, $this->createLazyContext());
        $commentPart->getContent();
    }

    /**
     * @param TokenLine[] $lines
     */
    #[DataProvider('getDataForTestGetDescription')]
    public function testGetDescription(string $expected, array $lines, int $prefixLength): void
    {
        $metadata = $this->createMock(TagMetadata::class);
        $metadata->method('getPrefixLength')->willReturn($prefixLength);
        $commentPart = new CommentPart(1, $metadata, $this->createLazyContext());
        array_walk($lines, static fn (TokenLine $line) => $commentPart->addLine($line));

        self::assertEquals($expected, $commentPart->getDescription());
    }

    /**
     * @param TokenLine[] $lines
     */
    #[DataProvider('getDataForTestGetSummary')]
    public function testGetSummary(string $expected, array $lines, int $prefixLength): void
    {
        $metadata = $this->createMock(TagMetadata::class);
        $metadata->method('getPrefixLength')->willReturn($prefixLength);
        $commentPart = new CommentPart(1, $metadata, $this->createLazyContext());
        array_walk($lines, static fn (TokenLine $line) => $commentPart->addLine($line));

        self::assertEquals($expected, $commentPart->getSummary());
    }

    /**
     * @param TokenLine[] $lines
     */
    #[DataProvider('getDataForTestInjectKey')]
    public function testInjectKey(
        string $expectedContent,
        string $key,
        IssueKeyPosition $position,
        int $prefixLength,
        ?int $separatorOffset,
        array $lines,
        ?string $newSeparator,
        bool $replaceSeparator,
    ): void {
        $tagMetadata = new TagMetadata(null, $prefixLength, null, $separatorOffset, null);
        $commentPart = $this->createCommentPartWithLines($lines, $tagMetadata);
        $commentPart->injectKey($key, $position, $newSeparator, $replaceSeparator);
        self::assertEquals($expectedContent, $commentPart->getContent());
    }

    public function testInjectKeyThrowsExceptionWithoutLines(): void
    {
        $this->expectException(NoLineException::class);
        $commentPart = new CommentPart(1, null, $this->createLazyContext());
        $commentPart->injectKey('any key', IssueKeyPosition::AFTER_SEPARATOR, null, false);
    }

    #[DataProvider('getDataForTestInjectKeyThrowsExceptionWithoutPrefix')]
    public function testInjectKeyThrowsExceptionWithoutPrefix(?int $prefixLength): void
    {
        $this->expectException(NoPrefixException::class);

        $commentPart = new CommentPart(1, new TagMetadata(null, $prefixLength, null, null, null), $this->createLazyContext());
        $commentPart->addLine(new TokenLine('', 'any text of line', '', ''));
        $commentPart->injectKey('any key', IssueKeyPosition::AFTER_SEPARATOR, null, false);
    }

    /**
     * @param TokenLine[] $lines
     */
    private function createCommentPartWithLines(array $lines, ?TagMetadata $tagMetadata = null): CommentPart
    {
        $commentPart = new CommentPart(1, $tagMetadata, $this->createLazyContext());
        foreach ($lines as $line) {
            $commentPart->addLine($line);
        }

        return $commentPart;
    }

    private function createLazyContext(): MappedContext
    {
        return new MappedContext(1, []);
    }
}
