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
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * @internal
 */
final readonly class CommentCleanerRegistry
{
    /**
     * @param iterable<CommentCleanerInterface> $cleaners
     */
    public function __construct(
        #[AutowireIterator('aeliot.todo_registrar.comment_cleaner')]
        private iterable $cleaners,
    ) {
    }

    public function getCleaner(TokenInterface $token): CommentCleanerInterface
    {
        foreach ($this->cleaners as $cleaner) {
            if ($cleaner->supports($token)) {
                return $cleaner;
            }
        }

        throw new \RuntimeException(\sprintf('No CommentCleaner supports token of type %s', $token::class));
    }
}
