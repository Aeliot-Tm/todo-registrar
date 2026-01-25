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
 * Result of file parsing: all tokens and comment nodes with context.
 *
 * @internal
 */
final readonly class ParsedFile
{
    /**
     * @param \PhpToken[] $allTokens
     * @param CommentNode[] $commentNodes
     */
    public function __construct(
        private \SplFileInfo $file,
        private array $allTokens,
        private array $commentNodes,
    ) {
    }

    /**
     * @return \PhpToken[]
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
