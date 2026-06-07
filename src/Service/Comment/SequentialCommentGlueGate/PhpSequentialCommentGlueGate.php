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

namespace Aeliot\TodoRegistrar\Service\Comment\SequentialCommentGlueGate;

use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * @internal
 */
#[AsTaggedItem(index: 'php')]
final class PhpSequentialCommentGlueGate extends AbstractSequentialCommentGlueGate
{
    protected function isGlueableComment(TokenInterface $token): bool
    {
        if (!$token->isComment()) {
            return false;
        }

        $text = ltrim($token->getText());

        return str_starts_with($text, '//') || str_starts_with($text, '#');
    }
}
