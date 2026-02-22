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

namespace Aeliot\TodoRegistrar\Dto\Token;

/**
 * Mutable adapter for \PhpToken.
 *
 * Wraps native PHP token and delegates all operations to it.
 * Mutations via setText() are applied directly to the wrapped PhpToken instance.
 *
 * @internal
 */
final class PhpTokenAdapter implements TokenInterface
{
    public function __construct(private \PhpToken $phpToken)
    {
    }

    public function getId(): int
    {
        return $this->phpToken->id;
    }

    public function getLine(): int
    {
        return $this->phpToken->line;
    }

    public function getText(): string
    {
        return $this->phpToken->text;
    }

    public function setText(string $text): void
    {
        $this->phpToken->text = $text;
    }

    public function isComment(): bool
    {
        return \in_array($this->phpToken->id, [\T_COMMENT, \T_DOC_COMMENT], true);
    }

    public function getCleanText(): string
    {
        if (!$this->isComment()) {
            return $this->phpToken->text;
        }

        if ($this->isSingleLineComment()) {
            return $this->cleanSingleLineComment();
        }

        return $this->cleanMultiLineComment();
    }

    public function isSingleLineComment(): bool
    {
        if (!$this->isComment()) {
            return false;
        }

        $text = ltrim($this->phpToken->text);

        return str_starts_with($text, '//') || str_starts_with($text, '#');
    }

    private function cleanSingleLineComment(): string
    {
        $lines = explode("\n", $this->phpToken->text);
        $cleanedLines = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\s*)(\/\/|#)/', $line, $matches)) {
                $cleanedLines[] = substr($line, \strlen($matches[1]) + \strlen($matches[2]));
            } else {
                $cleanedLines[] = $line;
            }
        }

        return implode("\n", $cleanedLines);
    }

    private function cleanMultiLineComment(): string
    {
        $lines = explode("\n", $this->phpToken->text);
        $cleanedLines = [];

        foreach ($lines as $index => $line) {
            if (0 === $index && preg_match('/^(\s*)(\/\*\*?)/', $line, $matches)) {
                $cleanedLines[] = substr($line, \strlen($matches[1]) + \strlen($matches[2]));
                continue;
            }

            if (preg_match('/^(\s*)(\*\/)/', $line)) {
                $cleanedLines[] = '';
                continue;
            }

            if (preg_match('/^(\s*)(\*)(?!\/)/', $line, $matches)) {
                $cleanedLines[] = substr($line, \strlen($matches[1]) + \strlen($matches[2]));
                continue;
            }

            $cleanedLines[] = $line;
        }

        return implode("\n", $cleanedLines);
    }
}
