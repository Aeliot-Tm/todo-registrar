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

use Aeliot\TodoRegistrar\Dto\Token\TokenStream;
use Aeliot\TodoRegistrar\Service\Comment\SequentialCommentGlueGate\YamlSequentialCommentGlueGate;
use Aeliot\TodoRegistrar\Service\File\Parser\YamlFileParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(YamlSequentialCommentGlueGate::class)]
final class YamlSequentialCommentGlueGateTest extends TestCase
{
    public function testCommentLineIsGlueable(): void
    {
        $stream = $this->createStreamFromFixture(__DIR__ . '/../../../../fixtures/comments_gluing/sequential_comments.yaml');

        while ('# TODO: title' !== $stream->current()?->getText()) {
            $stream->next();
        }

        self::assertTrue((new YamlSequentialCommentGlueGate())->canGlueCurrent($stream, false));
    }

    public function testNewlineBetweenCommentsIsGlueableWithActiveGroup(): void
    {
        $stream = $this->createStreamFromFixture(__DIR__ . '/../../../../fixtures/comments_gluing/sequential_comments.yaml');
        $afterFirstComment = false;

        while (!$stream->isEnd()) {
            $token = $stream->current();
            if (null === $token) {
                $stream->next();
                continue;
            }

            if ('# TODO: title' === $token->getText()) {
                $afterFirstComment = true;
                $stream->next();
                continue;
            }

            if ($afterFirstComment && "\n" === $token->getText()) {
                self::assertTrue((new YamlSequentialCommentGlueGate())->canGlueCurrent($stream, true));

                return;
            }

            $stream->next();
        }

        self::fail('Expected newline token after first YAML comment');
    }

    public function testScalarIsNotGlueable(): void
    {
        $stream = $this->createStreamFromFixture(__DIR__ . '/../../../../fixtures/comments_gluing/sequential_comments.yaml');

        self::assertFalse((new YamlSequentialCommentGlueGate())->canGlueCurrent($stream, false));
    }

    private function createStreamFromFixture(string $pathname): TokenStream
    {
        return (new YamlFileParser())->parse(new \SplFileInfo($pathname))->getTokenStream();
    }
}
