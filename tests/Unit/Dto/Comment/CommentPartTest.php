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
use Aeliot\TodoRegistrar\Exception\NoLineException;
use Aeliot\TodoRegistrar\Exception\NoPrefixException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentPart::class)]
#[UsesClass(TagMetadata::class)]
final class CommentPartTest extends TestCase
{
    /**
     * @return iterable<array{0: string, 1: array<string> }>
     */
    public static function getDataForTestGetContent(): iterable
    {
        yield ['abc', ['a', 'b', 'c']];
        yield ['bca', ['b', 'c', 'a']];

        $lines = [
            " * TODO: first line of description\n",
            " *       second line of description\n",
        ];
        yield [implode('', $lines), $lines];
    }

    /**
     * @return iterable<array{0: string, 1: array<string>, 2: int}>
     */
    public static function getDataForTestGetDescription(): iterable
    {
        yield [
            " second line of description\n" .
            " third line of description\n",
            [
                " * TODO: first line of description\n",
                " *       second line of description\n",
                " *       third line of description\n",
            ],
            8,
        ];

        yield [
            '',
            [
                ' # TODO: one line of description',
            ],
            8,
        ];
    }

    /**
     * @return iterable<array{0: string, 1: array<string>, 2: int}>
     */
    public static function getDataForTestGetSummary(): iterable
    {
        yield [
            'first line of description',
            [
                " * TODO: first line of description\n",
                " *       second line of description\n",
                " *       third line of description\n",
            ],
            8,
        ];

        yield [
            'one line of description',
            [
                ' # TODO: one line of description',
            ],
            8,
        ];
    }

    /**
     * @return iterable<array{0: string, 1: string, 2: int, 3: array<string>}>
     */
    public static function getDataForTestInjectKey(): iterable
    {
        yield ['TODO P-1 description', 'P-1', 4, ['TODO description']];
        yield [
            " * TODO: P-1 first line of description\n *       second line of description\n",
            'P-1',
            8,
            [
                " * TODO: first line of description\n",
                " *       second line of description\n",
            ],
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
     * @param string[] $lines
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
        $token = $this->createPhpToken();
        $commentPart = new CommentPart($token, null, $this->createLazyContext());
        $commentPart->getContent();
    }

    public function testGetFirstLineThrowsExceptionWithoutLines(): void
    {
        $this->expectException(NoLineException::class);
        $token = $this->createPhpToken();
        $commentPart = new CommentPart($token, null, $this->createLazyContext());
        $commentPart->getFirstLine();
    }

    /**
     * @param string[] $lines
     */
    #[DataProvider('getDataForTestGetDescription')]
    public function testGetDescription(string $expected, array $lines, int $prefixLength): void
    {
        $token = $this->createPhpToken();
        $metadata = $this->createMock(TagMetadata::class);
        $metadata->method('getPrefixLength')->willReturn($prefixLength);
        $commentPart = new CommentPart($token, $metadata, $this->createLazyContext());
        array_walk($lines, static fn (string $line) => $commentPart->addLine($line));

        self::assertEquals($expected, $commentPart->getDescription());
    }

    /**
     * @param string[] $lines
     */
    #[DataProvider('getDataForTestGetSummary')]
    public function testGetSummary(string $expected, array $lines, int $prefixLength): void
    {
        $token = $this->createPhpToken();
        $metadata = $this->createMock(TagMetadata::class);
        $metadata->method('getPrefixLength')->willReturn($prefixLength);
        $commentPart = new CommentPart($token, $metadata, $this->createLazyContext());
        array_walk($lines, static fn (string $line) => $commentPart->addLine($line));

        self::assertEquals($expected, $commentPart->getSummary());
    }

    /**
     * @param string[] $lines
     */
    #[DataProvider('getDataForTestInjectKey')]
    public function testInjectKey(string $expectedContent, string $key, int $prefixLength, array $lines): void
    {
        $tagMetadata = new TagMetadata(null, $prefixLength, null, null, null);
        $commentPart = $this->createCommentPartWithLines($lines, $tagMetadata);
        $commentPart->injectKey($key);
        self::assertEquals($expectedContent, $commentPart->getContent());
    }

    public function testInjectKeyThrowsExceptionWithoutLines(): void
    {
        $this->expectException(NoLineException::class);
        $token = $this->createPhpToken();
        $commentPart = new CommentPart($token, null, $this->createLazyContext());
        $commentPart->injectKey('any key');
    }

    #[DataProvider('getDataForTestInjectKeyThrowsExceptionWithoutPrefix')]
    public function testInjectKeyThrowsExceptionWithoutPrefix(?int $prefixLength): void
    {
        $this->expectException(NoPrefixException::class);

        $token = $this->createPhpToken();
        $commentPart = new CommentPart($token, new TagMetadata(null, $prefixLength, null, null, null), $this->createLazyContext());
        $commentPart->addLine('any text of line');
        $commentPart->injectKey('any key');
    }

    /**
     * @param string[] $lines
     */
    private function createCommentPartWithLines(array $lines, ?TagMetadata $tagMetadata = null): CommentPart
    {
        $token = $this->createPhpToken();
        $commentPart = new CommentPart($token, $tagMetadata, $this->createLazyContext());
        foreach ($lines as $line) {
            $commentPart->addLine($line);
        }

        return $commentPart;
    }

    private function createPhpToken(): \PhpToken
    {
        return new \PhpToken(\T_COMMENT, '// comment', 0, 0);
    }

    private function createLazyContext(): MappedContext
    {
        return new MappedContext(1, []);
    }
}
