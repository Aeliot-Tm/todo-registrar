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

/**
 * Wrapper around PhpToken with context.
 *
 * @internal
 */
final readonly class CommentNode
{
    public function __construct(
        private \PhpToken $token,
        private ContextInterface $context,
    ) {
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function getToken(): \PhpToken
    {
        return $this->token;
    }
}
