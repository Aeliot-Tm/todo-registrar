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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Comment\Gluing;

use Aeliot\TodoRegistrar\Dto\Token\PhpTokenAdapter;
use Aeliot\TodoRegistrar\Dto\Token\TokenStream;
use Aeliot\TodoRegistrar\Service\Comment\SequentialCommentGlueGate\PhpSequentialCommentGlueGate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpSequentialCommentGlueGate::class)]
final class PhpSequentialCommentGlueGateTest extends TestCase
{
    public function testGlueableSingleLineCommentWithoutActiveGroup(): void
    {
        $stream = new TokenStream([
            new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// TODO', 1, 1)),
        ]);

        self::assertTrue((new PhpSequentialCommentGlueGate())->canGlueCurrent($stream, false));
    }

    public function testBlockCommentIsNotGlueable(): void
    {
        $stream = new TokenStream([
            new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '/* block */', 1, 1)),
        ]);

        self::assertFalse((new PhpSequentialCommentGlueGate())->canGlueCurrent($stream, false));
    }

    public function testWhitespaceBridgesToNextComment(): void
    {
        $stream = new TokenStream([
            new PhpTokenAdapter(new \PhpToken(\T_WHITESPACE, "\n", 1, 10)),
            new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// next', 2, 1)),
        ]);

        self::assertTrue((new PhpSequentialCommentGlueGate())->canGlueCurrent($stream, true));
    }

    public function testParagraphBreakWhitespaceDoesNotBridge(): void
    {
        $stream = new TokenStream([
            new PhpTokenAdapter(new \PhpToken(\T_WHITESPACE, "\n\n", 1, 10)),
            new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// next', 3, 1)),
        ]);

        self::assertFalse((new PhpSequentialCommentGlueGate())->canGlueCurrent($stream, true));
    }
}
