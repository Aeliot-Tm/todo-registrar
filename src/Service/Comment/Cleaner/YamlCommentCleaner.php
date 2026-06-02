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

namespace Aeliot\TodoRegistrar\Service\Comment\Cleaner;

use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Aeliot\TodoRegistrar\Dto\Token\TokenLine;
use Aeliot\TodoRegistrar\Dto\Token\YamlTokenAdapter;
use Aeliot\TodoRegistrar\Service\Comment\CommentCleanerInterface;

/**
 * @internal
 */
final class YamlCommentCleaner implements CommentCleanerInterface
{
    /**
     * @return TokenLine[]
     */
    public function clean(string $commentText): array
    {
        if (preg_match('/^(\s*#\s?)(.*)$/', $commentText, $matches)) {
            return [new TokenLine($matches[1], $matches[2], '', '')];
        }

        return [new TokenLine('', $commentText, '', '')];
    }

    public function supports(TokenInterface $token): bool
    {
        return $token instanceof YamlTokenAdapter;
    }
}
