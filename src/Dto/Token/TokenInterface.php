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
 * Abstraction for file tokens that allows working with different file types.
 *
 * @internal
 */
interface TokenInterface
{
    /**
     * Get token identifier (e.g., T_COMMENT, T_DOC_COMMENT for PHP).
     */
    public function getId(): int;

    /**
     * Get token text content.
     */
    public function getText(): string;

    /**
     * Get line number where token appears.
     */
    public function getLine(): int;

    /**
     * Set token text content (mutable for comment modification after key injection).
     */
    public function setText(string $text): void;
}
