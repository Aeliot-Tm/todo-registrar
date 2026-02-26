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
 * Helper class for grouping consecutive single-line comment tokens.
 *
 * @internal
 */
final class CommentTokensGroup
{
    /**
     * @var TokenInterface[]
     */
    private array $tokens = [];

    /**
     * @var TokenInterface[]
     */
    private array $pendingWhitespace = [];

    public function addToken(TokenInterface $token): void
    {
        // Flush pending whitespace before adding new token
        if (!empty($this->pendingWhitespace)) {
            array_push($this->tokens, ...$this->pendingWhitespace);
            $this->pendingWhitespace = [];
        }

        $this->tokens[] = $token;
    }

    public function addWhitespace(TokenInterface $token): void
    {
        $this->pendingWhitespace[] = $token;
    }

    public function hasPendingWhitespace(): bool
    {
        return !empty($this->pendingWhitespace);
    }

    public function isEmpty(): bool
    {
        return empty($this->tokens);
    }

    /**
     * @return TokenInterface[]
     */
    public function grabTokens(): array
    {
        $tokens = $this->tokens;
        $this->tokens = [];
        $this->pendingWhitespace = [];

        return $tokens;
    }
}
