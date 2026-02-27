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
final class TokenLinesStack
{
    /**
     * @var TokenLine[]
     */
    private array $lines = [];

    public function __construct(private readonly TokenInterface $token)
    {
    }

    public function addLine(TokenLine $line): void
    {
        $this->lines[] = $line;
    }

    public function flush(): void
    {
        $this->token->setText(implode('', array_map(
            static fn (TokenLine $l): string => $l->reconstruct(),
            $this->lines,
        )));
    }
}
