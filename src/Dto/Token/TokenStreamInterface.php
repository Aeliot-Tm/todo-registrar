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
 *
 * @extends \Iterator<TokenInterface>
 */
interface TokenStreamInterface extends \Iterator
{
    public function current(): ?TokenInterface;

    /**
     * @return array<TokenInterface>
     */
    public function getTokens(): array;

    public function isEnd(): bool;

    public function peek(int $offset): ?TokenInterface;
}
