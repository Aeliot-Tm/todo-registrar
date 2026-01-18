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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\File;

use Aeliot\TodoRegistrar\Dto\Parsing\CommentNode;
use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Service\File\FileParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileParser::class)]
#[UsesClass(TagMetadata::class)]
final class ParserTest extends TestCase
{
    public static function getDataForTestParsing(): iterable
    {
        yield [
            '// TODO single line comment',
            __DIR__ . '/../../../fixtures/single_line.php',
        ];

        yield [
            <<<CONT
/*
 * Multi line comment
 */
CONT,
            __DIR__ . '/../../../fixtures/multi_line_comment.php',
        ];

        yield [
            <<<CONT
/**
 * Multi line doc block
 */
CONT,
            __DIR__ . '/../../../fixtures/multi_line_doc_block.php',
        ];
    }

    #[DataProvider('getDataForTestParsing')]
    public function testParsing(string $expectedText, string $path): void
    {
        $commentNodes = $this->getCommentNodes($path);
        self::assertCount(1, $commentNodes);
        self::assertSame($expectedText, $commentNodes[0]->token->text);
    }

    /**
     * @return CommentNode[]
     */
    private function getCommentNodes(string $path): array
    {
        $parsedFile = (new FileParser())->parse($this->getMockSplFileInfo($path));

        return $parsedFile->getCommentNodes();
    }

    private function getMockSplFileInfo(string $pathname): \SplFileInfo&MockObject
    {
        $file = $this->createMock(\SplFileInfo::class);
        $file->method('getPathname')->willReturn($pathname);

        return $file;
    }
}
