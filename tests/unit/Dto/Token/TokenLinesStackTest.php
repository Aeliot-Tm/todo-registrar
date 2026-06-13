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

namespace Aeliot\TodoRegistrar\Test\Unit\Dto\Token;

use Aeliot\TodoRegistrar\Dto\Token\PhpTokenAdapter;
use Aeliot\TodoRegistrar\Dto\Token\TokenLine;
use Aeliot\TodoRegistrar\Dto\Token\TokenLinesStack;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TokenLinesStack::class)]
#[UsesClass(TokenLine::class)]
final class TokenLinesStackTest extends TestCase
{
    public function testFlushEmptyLines(): void
    {
        $phpToken = new \PhpToken(\T_COMMENT, '');
        $token = new PhpTokenAdapter($phpToken);
        $writer = new TokenLinesStack($token);

        $writer->flush();

        self::assertSame('', $token->getText());
    }

    public function testFlushMultipleLines(): void
    {
        $original = "/**\n * TODO: summary\n */";
        $phpToken = new \PhpToken(\T_DOC_COMMENT, $original);
        $token = new PhpTokenAdapter($phpToken);
        $writer = new TokenLinesStack($token);
        $writer->addLine(new TokenLine('/**', '', '', "\n"));
        $writer->addLine(new TokenLine(' * ', 'TODO: #123 summary', '', "\n"));
        $writer->addLine(new TokenLine('', '', ' */', ''));

        $writer->flush();

        self::assertSame("/**\n * TODO: #123 summary\n */", $token->getText());
    }

    public function testFlushSingleLine(): void
    {
        $phpToken = new \PhpToken(\T_COMMENT, '// original');
        $token = new PhpTokenAdapter($phpToken);
        $writer = new TokenLinesStack($token);
        $writer->addLine(new TokenLine('// ', 'modified', '', ''));

        $writer->flush();

        self::assertSame('// modified', $token->getText());
    }
}
