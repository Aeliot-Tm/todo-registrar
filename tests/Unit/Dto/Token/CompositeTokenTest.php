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

use Aeliot\TodoRegistrar\Dto\Token\CompositeToken;
use Aeliot\TodoRegistrar\Dto\Token\PhpTokenAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompositeToken::class)]
#[UsesClass(PhpTokenAdapter::class)]
final class CompositeTokenTest extends TestCase
{
    public function testGetTextConcatenatesAllTokensAndClearsRemainingTokens(): void
    {
        $token1 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// TODO: task 1', 1, 0));
        $token2 = new PhpTokenAdapter(new \PhpToken(\T_WHITESPACE, "\n", 1, 0));
        $token3 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// TODO: task 2', 2, 0));
        $token4 = new PhpTokenAdapter(new \PhpToken(\T_WHITESPACE, "\n", 2, 0));
        $token5 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// description', 3, 0));

        $composite = new CompositeToken([$token1, $token2, $token3, $token4, $token5]);

        $expected = "// TODO: task 1\n// TODO: task 2\n// description";
        self::assertSame($expected, $composite->getText());
        self::assertSame('', $token2->getText());
        self::assertSame('', $token3->getText());
        self::assertSame('', $token4->getText());
        self::assertSame('', $token5->getText());
    }

    public function testSetTextPutsAllTextIntoFirstToken(): void
    {
        $token1 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// TODO: task 1', 1, 0));
        $token2 = new PhpTokenAdapter(new \PhpToken(\T_WHITESPACE, "\n", 1, 0));
        $token3 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// TODO: task 2', 2, 0));
        $token4 = new PhpTokenAdapter(new \PhpToken(\T_WHITESPACE, "\n", 2, 0));
        $token5 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// description', 3, 0));

        $composite = new CompositeToken([$token1, $token2, $token3, $token4, $token5]);

        $newText = "// TODO: KEY-1 task 1\n// TODO: KEY-2 task 2\n// description";
        $composite->setText($newText);

        self::assertSame($newText, $token1->getText());
        self::assertSame($newText, $composite->getText());
        self::assertSame('', $token2->getText());
        self::assertSame('', $token3->getText());
        self::assertSame('', $token4->getText());
        self::assertSame('', $token5->getText());
    }

    public function testGetIdDelegatesToFirstToken(): void
    {
        $token1 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// comment', 1, 0));
        $token2 = new PhpTokenAdapter(new \PhpToken(\T_DOC_COMMENT, '/** doc */', 2, 0));

        $composite = new CompositeToken([$token1, $token2]);

        self::assertSame(\T_COMMENT, $composite->getId());
    }

    public function testGetLineDelegatesToFirstToken(): void
    {
        $token1 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// comment', 5, 0));
        $token2 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// comment', 6, 0));

        $composite = new CompositeToken([$token1, $token2]);

        self::assertSame(5, $composite->getLine());
    }

    public function testSaverScenarioNoDuplication(): void
    {
        // Simulate Saver scenario: allTokens array
        $token1 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// TODO: task 1', 1, 0));
        $token2 = new PhpTokenAdapter(new \PhpToken(\T_WHITESPACE, "\n", 1, 0));
        $token3 = new PhpTokenAdapter(new \PhpToken(\T_COMMENT, '// TODO: task 2', 2, 0));
        $token4 = new PhpTokenAdapter(new \PhpToken(\T_STRING, '$var', 3, 0));

        // Create composite wrapping token1, token2, and token3 (same object references!)
        $composite = new CompositeToken([$token1, $token2, $token3]);

        $newText = "// TODO: KEY-1 task 1\n// TODO: KEY-2 task 2";
        $composite->setText($newText);

        // Saver concatenates all tokens
        $content = implode('', array_map(static fn ($t) => $t->getText(), [$token1, $token2, $token3, $token4]));

        // Should be: "full text" + "" + "" + "$var" = no duplication!
        self::assertSame($newText . '$var', $content);
    }

    public function testEmptyTokensArrayThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CompositeToken requires at least one token');

        new CompositeToken([]);
    }
}
