<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Unit\Dto\Comment;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Exception\NoPrefixException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentPart::class)]
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
            ]
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
        $this->expectException(\RuntimeException::class);
        $commentPart = new CommentPart(null);
        $commentPart->getContent();
    }

    public function testGetFirstLineThrowsExceptionWithoutLines(): void
    {
        $this->expectException(\RuntimeException::class);
        $commentPart = new CommentPart(null);
        $commentPart->getFirstLine();
    }

    /**
     * @param string[] $lines
     */
    #[DataProvider('getDataForTestInjectKey')]
    public function testInjectKey(string $expectedContent, string $key, int $prefixLength, array $lines): void
    {
        $tagMetadata = new TagMetadata(null, $prefixLength);
        $commentPart = $this->createCommentPartWithLines($lines, $tagMetadata);
        $commentPart->injectKey($key);
        self::assertEquals($expectedContent, $commentPart->getContent());
    }

    public function testInjectKeyThrowsExceptionWithoutLines(): void
    {
        $this->expectException(\RuntimeException::class);
        $commentPart = new CommentPart(null);
        $commentPart->injectKey('any key');
    }

    #[DataProvider('getDataForTestInjectKeyThrowsExceptionWithoutPrefix')]
    public function testInjectKeyThrowsExceptionWithoutPrefix(?int $prefixLength): void
    {
        $this->expectException(NoPrefixException::class);

        $commentPart = new CommentPart(new TagMetadata(null, $prefixLength));
        $commentPart->addLine('any text of line');
        $commentPart->injectKey('any key');
    }

    /**
     * @param string[] $lines
     */
    private function createCommentPartWithLines(array $lines, ?TagMetadata $tagMetadata = null): CommentPart
    {
        $commentPart = new CommentPart($tagMetadata);
        foreach ($lines as $line) {
            $commentPart->addLine($line);
        }
        return $commentPart;
    }
}