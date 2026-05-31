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

use Aeliot\YamlToken\Node\CommentNode;
use Aeliot\YamlToken\Node\Node;
use Aeliot\YamlToken\Node\TokenHolderInterface;

/**
 * Mutable adapter for yaml {@see Node}.
 *
 * Wraps native PHP token and delegates all operations to it.
 * Mutations via setText() are applied directly to the wrapped PhpToken instance.
 *
 * @internal
 */
final class YamlTokenAdapter implements TokenInterface
{
    public function __construct(private readonly Node&TokenHolderInterface $yamlNode)
    {
    }

    public function getLine(): int
    {
        return $this->yamlNode->getToken()->line;
    }

    public function getText(): string
    {
        return $this->yamlNode->getToken()->text;
    }

    public function setText(string $text): void
    {
        $this->yamlNode->getToken()->text = $text;
    }

    public function isComment(): bool
    {
        return $this->yamlNode instanceof CommentNode;
    }

    public function isSingleLineComment(): bool
    {
        return true;
    }
}
