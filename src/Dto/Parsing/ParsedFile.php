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
 * Result of file parsing: all tokens and comment nodes with context.
 *
 * @internal
 */
final readonly class ParsedFile
{
    /**
     * @param TokenInterface[] $allTokens
     * @param CommentNode[] $commentNodes
     */
    public function __construct(
        private \SplFileInfo $file,
        private array $allTokens,
        private array $commentNodes,
    ) {
    }

    /**
     * @return TokenInterface[]
     */
    public function getAllTokens(): array
    {
        return $this->allTokens;
    }

    /**
     * @return CommentNode[]
     */
    public function getCommentNodes(): array
    {
        return $this->commentNodes;
    }

    public function getFile(): \SplFileInfo
    {
        return $this->file;
    }
}
