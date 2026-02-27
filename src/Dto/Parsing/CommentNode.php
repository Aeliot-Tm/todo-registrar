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

namespace Aeliot\TodoRegistrar\Dto\Parsing;

use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;

/**
 * Wrapper around one or more comment tokens with context.
 *
 * @internal
 */
final readonly class CommentNode
{
    /**
     * @param TokenInterface[] $tokens
     */
    public function __construct(
        private array $tokens,
        private ContextInterface $context,
    ) {
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * @return TokenInterface[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }
}
