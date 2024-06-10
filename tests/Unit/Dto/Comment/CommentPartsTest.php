<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Dto\Comment;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Comment\CommentParts;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentParts::class)]
final class CommentPartsTest extends TestCase
{
    /**
     * @return iterable<array{0: string, 1: array<string> }>
     */
    public static function getDataForTestGetContent(): iterable
    {
        yield ['abc', ['a', 'b', 'c']];
        yield ['bca', ['b', 'c', 'a']];

        $lines = ["/*\n", " * TODO\n", " */\n"];
        yield [implode('', $lines), $lines];
    }

    public function testChoiceOfPartByTag(): void
    {
        $commentParts = new CommentParts();
        $commentParts->addPart($this->mockPartWithTag(null));
        $commentParts->addPart($this->mockPartWithTag('A'));
        $commentParts->addPart($this->mockPartWithTag(null));
        $commentParts->addPart($this->mockPartWithTag('B'));
        $commentParts->addPart($this->mockPartWithTag(null));

        $parts = array_values($commentParts->getParts());
        self::assertCount(5, $parts);
        self::assertNull($parts[0]->getTag());
        self::assertEquals('A', $parts[1]->getTag());
        self::assertNull($parts[2]->getTag());
        self::assertEquals('B', $parts[3]->getTag());
        self::assertNull($parts[4]->getTag());

        $todos = array_values($commentParts->getTodos());
        self::assertCount(2, $todos);
        self::assertEquals('A', $todos[0]->getTag());
        self::assertEquals('B', $todos[1]->getTag());
    }

    /**
     * @param string[] $lines
     */
    #[DataProvider('getDataForTestGetContent')]
    public function testGetContent(string $expectedContent, array $lines): void
    {
        $commentParts = new CommentParts();
        foreach ($lines as $line) {
            $commentParts->addPart($this->mockPartWithContent($line));
        }
        self::assertEquals($expectedContent, $commentParts->getContent());
    }

    private function mockPartWithContent(string $content): CommentPart&MockObject
    {
        $part = $this->createMock(CommentPart::class);
        $part->method('getContent')->willReturn($content);

        return $part;
    }

    private function mockPartWithTag(?string $tag): CommentPart&MockObject
    {
        $part = $this->createMock(CommentPart::class);
        $part->method('getTag')->willReturn($tag);

        return $part;
    }
}