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

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @internal
 */
final readonly class SequentialCommentGlueGateRegistry
{
    /**
     * @param ServiceLocator<SequentialCommentGlueGateInterface> $gates
     */
    public function __construct(
        #[AutowireLocator('aeliot.todo_registrar.sequential_comment_glue_gate')]
        private ServiceLocator $gates,
    ) {
    }

    public function find(string $extension): ?SequentialCommentGlueGateInterface
    {
        if (!$this->gates->has($extension)) {
            return null;
        }

        return $this->gates->get($extension);
    }
}
