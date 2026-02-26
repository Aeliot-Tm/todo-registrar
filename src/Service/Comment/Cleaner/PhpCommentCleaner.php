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

namespace Aeliot\TodoRegistrar\Service\Comment\Cleaner;

use Aeliot\TodoRegistrar\Dto\Token\PhpTokenAdapter;
use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Aeliot\TodoRegistrar\Dto\Token\TokenLine;
use Aeliot\TodoRegistrar\Service\Comment\CommentCleanerInterface;

/**
 * @internal
 */
final class PhpCommentCleaner implements CommentCleanerInterface
{
    /**
     * @return TokenLine[]
     */
    public function clean(string $commentText): array
    {
        $trimmed = ltrim($commentText);

        if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '#')) {
            return $this->cleanSingleLineComment($commentText);
        }

        return $this->cleanBlockComment($commentText);
    }

    public function supports(TokenInterface $token): bool
    {
        return $token instanceof PhpTokenAdapter;
    }

    /**
     * @return TokenLine[]
     */
    private function cleanBlockComment(string $text): array
    {
        $lines = $this->splitLines($text);
        $lineCount = \count($lines);
        $result = [];

        foreach ($lines as $i => $line) {
            [$lineBody, $eol] = $this->separateEol($line);
            $isLast = ($i === $lineCount - 1);

            if (0 === $i) {
                $result[] = $this->cleanBlockFirstLine($lineBody, $eol, 1 === $lineCount);
            } elseif ($isLast) {
                $result[] = $this->cleanBlockLastLine($lineBody, $eol);
            } else {
                $result[] = $this->cleanBlockMiddleLine($lineBody, $eol);
            }
        }

        return $result;
    }

    private function cleanBlockFirstLine(string $line, string $eol, bool $isSingleLine): TokenLine
    {
        $suffix = '';

        if ($isSingleLine) {
            [$line, $suffix] = $this->extractBlockSuffix($line);
        }

        if (preg_match('/^(\s*\/\*+\s?)(.*)$/', $line, $matches)) {
            return new TokenLine($matches[1], $matches[2], $suffix, $eol);
        }

        return new TokenLine('', $line, $suffix, $eol);
    }

    private function cleanBlockLastLine(string $line, string $eol): TokenLine
    {
        [$line, $suffix] = $this->extractBlockSuffix($line);

        if ('' !== $line && preg_match('/^(\s*\*\s?)(.*)$/', $line, $matches)) {
            return new TokenLine($matches[1], $matches[2], $suffix, $eol);
        }

        return new TokenLine($line, '', $suffix, $eol);
    }

    private function cleanBlockMiddleLine(string $line, string $eol): TokenLine
    {
        if (preg_match('/^(\s*\*\s?)(.*)$/', $line, $matches)) {
            return new TokenLine($matches[1], $matches[2], '', $eol);
        }

        if (preg_match('/^(\s+)(.+)$/', $line, $matches)) {
            return new TokenLine($matches[1], $matches[2], '', $eol);
        }

        return new TokenLine('', $line, '', $eol);
    }

    /**
     * @return TokenLine[]
     */
    private function cleanSingleLineComment(string $text): array
    {
        $result = [];

        foreach ($this->splitLines($text) as $line) {
            [$lineBody, $eol] = $this->separateEol($line);

            if (preg_match('/^(\s*(?:\/\/|#)\s?)(.*)$/', $lineBody, $matches)) {
                $result[] = new TokenLine($matches[1], $matches[2], '', $eol);
            } else {
                $result[] = new TokenLine('', $lineBody, '', $eol);
            }
        }

        return $result;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function extractBlockSuffix(string $line): array
    {
        if (preg_match('/^(.*?)(\s?\*\/)$/', $line, $matches)) {
            return [$matches[1], $matches[2]];
        }

        return [$line, ''];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function separateEol(string $line): array
    {
        foreach (["\r\n", "\n", "\r"] as $eol) {
            if (str_ends_with($line, $eol)) {
                return [substr($line, 0, \strlen($eol) * -1), $eol];
            }
        }

        return [$line, ''];
    }

    /**
     * @return string[]
     */
    private function splitLines(string $text): array
    {
        if (preg_match_all('/[^\r\n]*(?:\r\n|\r|\n)|[^\r\n]+/', $text, $matches)) {
            return $matches[0];
        }

        return [$text];
    }
}
