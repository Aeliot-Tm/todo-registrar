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

namespace Aeliot\TodoRegistrar\Service\Comment;

use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Aeliot\TodoRegistrar\Dto\Token\TokenLine;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @internal
 */
#[AutoconfigureTag('aeliot.todo_registrar.comment_cleaner')]
interface CommentCleanerInterface
{
    /**
     * @return TokenLine[]
     */
    public function clean(string $commentText): array;

    public function supports(TokenInterface $token): bool;
}
