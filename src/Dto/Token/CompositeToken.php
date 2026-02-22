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
 * Composite token that wraps multiple single-line comment tokens.
 * Presents them as a single multi-line comment for processing.
 *
 * @internal
 */
final class CompositeToken implements TokenInterface
{
    /**
     * @var TokenInterface[]
     */
    private array $tokens;

    /**
     * @param TokenInterface[] $tokens
     */
    public function __construct(array $tokens)
    {
        if (empty($tokens)) {
            throw new \InvalidArgumentException('CompositeToken requires at least one token');
        }
        $this->tokens = array_values($tokens);

        // Put ALL text into the first token, preserving original whitespace (line breaks) between comments
        $this->tokens[0]->setText(implode('', array_map(
            static fn (TokenInterface $t): string => $t->getText(),
            $this->tokens,
        )));

        // Clear all remaining tokens to prevent duplication when Saver iterates
        for ($i = 1, $iMax = \count($this->tokens); $i < $iMax; ++$i) {
            $this->tokens[$i]->setText('');
        }
    }

    public function getId(): int
    {
        return $this->tokens[0]->getId();
    }

    public function getText(): string
    {
        return $this->tokens[0]->getText();
    }

    public function getCleanText(): string
    {
        return $this->tokens[0]->getCleanText();
    }

    public function getLine(): int
    {
        return $this->tokens[0]->getLine();
    }

    public function setText(string $text): void
    {
        $this->tokens[0]->setText($text);
    }

    public function isComment(): bool
    {
        return $this->tokens[0]->isComment();
    }

    public function isSingleLineComment(): bool
    {
        return false;
    }
}
