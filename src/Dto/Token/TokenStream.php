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
 * @internal
 */
final class TokenStream implements TokenStreamInterface
{
    private int $position = 0;

    /**
     * @param array<TokenInterface> $tokens
     */
    public function __construct(private array $tokens)
    {
    }

    public function current(): ?TokenInterface
    {
        return $this->tokens[$this->position] ?? null;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function isEnd(): bool
    {
        return $this->position >= \count($this->tokens);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function peek(int $offset): ?TokenInterface
    {
        return $this->tokens[$this->position + $offset] ?? null;
    }

    public function rewind(): void
    {
        reset($this->tokens);
    }

    public function valid(): bool
    {
        return isset($this->tokens[$this->position]);
    }
}
