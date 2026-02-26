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
final class CommentParts
{
    /**
     * @var CommentPart[]
     */
    private array $todos = [];

    public function addPart(CommentPart $part): void
    {
        if (null !== $part->getTag()) {
            $this->todos[] = $part;
        }
    }

    /**
     * @return CommentPart[]
     */
    public function getTodos(): array
    {
        return $this->todos;
    }
}
