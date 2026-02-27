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
use Aeliot\TodoRegistrar\Dto\Parsing\CommentNode;
use Aeliot\TodoRegistrar\Dto\Parsing\MappedContext;
use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Dto\Token\PhpTokenAdapter;
use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Aeliot\TodoRegistrar\Dto\Token\TokenLine;
use Aeliot\TodoRegistrar\Dto\Token\TokenLinesStack;
use Aeliot\TodoRegistrar\Service\Comment\Cleaner\PhpCommentCleaner;
use Aeliot\TodoRegistrar\Service\Comment\CommentCleanerRegistry;
use Aeliot\TodoRegistrar\Service\Comment\Extractor;
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Extractor::class)]
#[UsesClass(CommentCleanerRegistry::class)]
#[UsesClass(CommentNode::class)]
#[UsesClass(CommentPart::class)]
#[UsesClass(PhpCommentCleaner::class)]
#[UsesClass(TagDetector::class)]
#[UsesClass(TagMetadata::class)]
#[UsesClass(TokenLine::class)]
#[UsesClass(TokenLinesStack::class)]
final class ExtractorTest extends TestCase
{
    public static function getDataForTestCountOfParts(): iterable
    {
        yield [
            1,
            '// TODO single line comment',
        ];

        yield [
            1,
            '# TODO single line comment',
        ];

        yield [
            1,
            <<<CONT
# TODO single line comment
#      with some extra part
CONT,
        ];

        yield [
            1,
            <<<CONT
/*
 * TODO: inside of multi line comment
 */
CONT,
        ];

        yield [
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
    public function testCountOfParts(int $expectedTodoCount, string $comment): void
    {
        $parts = $this->createExtractor()->extract($this->createCommentNode($comment));
        self::assertCount($expectedTodoCount, $parts);
    }

    private function createCommentNode(string $comment): CommentNode
    {
        return new CommentNode([$this->createPhpToken($comment)], $this->createLazyContext());
    }

    private function createExtractor(): Extractor
    {
        return new Extractor(
            new TagDetector(['todo', 'fixme'], [':', '-']),
            new CommentCleanerRegistry([new PhpCommentCleaner()]),
        );
    }

    private function createPhpToken(string $comment): TokenInterface
    {
        // Fallback: create a token manually if no comment found
        return new PhpTokenAdapter(new \PhpToken(\T_COMMENT, $comment, 0, 0));
    }

    private function createLazyContext(): MappedContext
    {
        return new MappedContext(1, []);
    }
}
