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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Comment;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Comment\CommentParts;
use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Service\Comment\Extractor;
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Extractor::class)]
#[UsesClass(CommentPart::class)]
#[UsesClass(CommentParts::class)]
#[UsesClass(TagDetector::class)]
#[UsesClass(TagMetadata::class)]
final class ExtractorTest extends TestCase
{
    public static function getDataForTestCatchLineSeparator(): iterable
    {
        yield [
            <<<CONT
/*
 * TODO: multi line comment
 *       with some extra description.
 */
CONT,
        ];
    }

    public static function getDataForTestCountOfParts(): iterable
    {
        yield [
            1,
            1,
            '// TODO single line comment',
        ];

        yield [
            1,
            1,
            '# TODO single line comment',
        ];

        yield [
            1,
            1,
            <<<CONT
# TODO single line comment
#      with some extra part
CONT,
        ];

        yield [
            3,
            1,
            <<<CONT
/*
 * TODO: inside of multi line comment
 */
CONT,
        ];

        yield [
            3,
            1,
            <<<CONT
/*
 * TODO: multi line comment
 *       with some extra description.
 */
CONT,
        ];
    }

    #[DataProvider('getDataForTestCatchLineSeparator')]
    public function testCatchLineSeparator(string $comment): void
    {
        $token = $this->createPhpToken($comment);
        $parts = (new Extractor(new TagDetector()))->extract($comment, $token);
        self::assertSame($comment, $parts->getContent());
    }

    #[DataProvider('getDataForTestCountOfParts')]
    public function testCountOfParts(int $expectedTotalCount, int $expectedTodoCount, string $comment): void
    {
        $token = $this->createPhpToken($comment);
        $parts = (new Extractor(new TagDetector()))->extract($comment, $token);
        self::assertCount($expectedTotalCount, $parts->getParts());
        self::assertCount($expectedTodoCount, $parts->getTodos());
    }

    private function createPhpToken(string $comment): \PhpToken
    {
        // Fallback: create a token manually if no comment found
        return new \PhpToken(\T_COMMENT, $comment, 0, 0);
    }
}
