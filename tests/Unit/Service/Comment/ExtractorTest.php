<?php

declare(strict_types=1);

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
            <<<CON
# TODO single line comment
#      with some extra part
CON,
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

    #[DataProvider('getDataForTestCountOfParts')]
    public function testCountOfParts(int $expectedTotalCount, int $expectedTodoCount, string $comment): void
    {
        $parts = (new Extractor(new TagDetector()))->extract($comment);
        self::assertCount($expectedTotalCount, $parts->getParts());
        self::assertCount($expectedTodoCount, $parts->getTodos());
    }
}