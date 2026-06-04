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

use Aeliot\TodoRegistrar\Dto\Token\TokenStreamInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @internal
 */
#[AutoconfigureTag('aeliot.todo_registrar.sequential_comment_glue_gate')]
interface SequentialCommentGlueGateInterface
{
    public function canGlueCurrent(TokenStreamInterface $stream, bool $hasActiveGroup): bool;
}
