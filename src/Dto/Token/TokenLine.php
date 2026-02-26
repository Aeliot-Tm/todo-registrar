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
final class TokenLine
{
    public function __construct(
        private readonly string $prefix,
        private string $content,
        private readonly string $suffix,
        private readonly string $eol,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getEol(): string
    {
        return $this->eol;
    }

    public function reconstruct(): string
    {
        return $this->prefix . $this->content . $this->suffix . $this->eol;
    }
}
