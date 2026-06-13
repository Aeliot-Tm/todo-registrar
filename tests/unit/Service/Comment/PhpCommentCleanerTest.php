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

use Aeliot\TodoRegistrar\Dto\Token\PhpTokenAdapter;
use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Aeliot\TodoRegistrar\Dto\Token\TokenLine;
use Aeliot\TodoRegistrar\Service\Comment\Cleaner\PhpCommentCleaner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpCommentCleaner::class)]
#[UsesClass(TokenLine::class)]
final class PhpCommentCleanerTest extends TestCase
{
    public static function getDataForTestCleanContent(): iterable
    {
        yield 'single-line //' => [
            ['TODO: summary'],
            '// TODO: summary',
        ];

        yield 'single-line // no space' => [
            ['TODO: summary'],
            '//TODO: summary',
        ];

        yield 'single-line #' => [
            ['TODO: summary'],
            '# TODO: summary',
        ];

        yield 'single-line block' => [
            ['TODO: summary'],
            '/* TODO: summary */',
        ];

        yield 'single-line doc' => [
            ['TODO: summary'],
            '/** TODO: summary */',
        ];

        yield 'multi-line doc' => [
            ['', 'TODO: summary', '      description', ''],
            "/**\n * TODO: summary\n *       description\n */",
        ];

        yield 'multi-line doc with content on first line' => [
            ['TODO: summary', '       description', ''],
            "/** TODO: summary\n *        description\n */",
        ];

        yield 'block with missing asterisks' => [
            ['', 'TODO: summary', 'description', ''],
            "/*\n   TODO: summary\n   description\n*/",
        ];

        yield 'empty block' => [
            [''],
            '/**/',
        ];

        yield 'doc with empty line' => [
            ['', 'TODO: summary', '', 'description', ''],
            "/**\n * TODO: summary\n *\n * description\n */",
        ];
    }

    public static function getDataForTestReconstructionInvariant(): iterable
    {
        yield 'single-line //' => ['// TODO: summary'];
        yield 'single-line #' => ['# TODO: summary'];
        yield 'single-line /* */' => ['/* TODO: summary */'];
        yield 'single-line /** */' => ['/** TODO: summary */'];
        yield 'empty block /**/  ' => ['/**/'];
        yield 'multi-line block' => [
            "/*\n * TODO: summary\n *       description\n */",
        ];
        yield 'multi-line doc' => [
            "/**\n * TODO: summary\n *       description\n */",
        ];
        yield 'doc with content on first line' => [
            "/** TODO: summary\n *        description\n */",
        ];
        yield 'block with missing asterisks' => [
            "/*\n   TODO: summary\n   description\n*/",
        ];
        yield 'block with CRLF' => [
            "/*\r\n * TODO: summary\r\n */",
        ];
        yield '// with no space after marker' => ['//TODO: summary'];
        yield '# with no space after marker' => ['#TODO: summary'];
        yield 'block with content on last line' => [
            "/*\n * TODO: summary\n * description */",
        ];
        yield 'single-line block no spaces' => ['/*comment*/'];
        yield 'doc with empty lines' => [
            "/**\n * TODO: summary\n *\n * description\n */",
        ];
    }

    public function testDoesNotSupportOtherTokens(): void
    {
        $token = $this->createMock(TokenInterface::class);
        self::assertFalse((new PhpCommentCleaner())->supports($token));
    }

    /**
     * @param string[] $expectedContents
     */
    #[DataProvider('getDataForTestCleanContent')]
    public function testCleanContent(array $expectedContents, string $comment): void
    {
        self::assertSame($expectedContents, array_map(
            static fn (TokenLine $line): string => $line->getContent(),
            (new PhpCommentCleaner())->clean($comment),
        ));
    }

    #[DataProvider('getDataForTestReconstructionInvariant')]
    public function testReconstructionInvariant(string $comment): void
    {
        self::assertSame($comment, implode('', array_map(
            static fn (TokenLine $line): string => $line->reconstruct(),
            (new PhpCommentCleaner())->clean($comment),
        )));
    }

    public function testSupportsPhpTokenAdapter(): void
    {
        $token = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// test'));
        self::assertTrue((new PhpCommentCleaner())->supports($token));
    }
}
