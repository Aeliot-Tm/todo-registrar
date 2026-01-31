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

namespace Aeliot\TodoRegistrar\Dto\Comment;

/**
 * @internal
 */
final class MiddleSpace
{
    private string $tail = '';

    public function __construct(private readonly string $defaultSymbol = ' ')
    {
    }

    public function addSpace(string $space): void
    {
        $this->tail .= $space;
    }

    public function getSpace(): string
    {
        $symbol = $this->defaultSymbol;
        if ('' !== $this->tail) {
            $symbol = $this->tail[0];
            $this->tail = substr($this->tail, 1);
        }

        return $symbol;
    }

    public function grabTail(): string
    {
        $tail = $this->tail;
        $this->tail = '';

        return $tail;
    }
}
