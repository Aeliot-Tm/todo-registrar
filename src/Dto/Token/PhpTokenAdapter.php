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
}
