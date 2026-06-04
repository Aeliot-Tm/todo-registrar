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
use Aeliot\TodoRegistrar\Dto\Token\TokenStreamInterface;
use Aeliot\TodoRegistrar\Service\Comment\SequentialCommentGlueGateInterface;

/**
 * @internal
 */
abstract class AbstractSequentialCommentGlueGate implements SequentialCommentGlueGateInterface
{
    public function canGlueCurrent(TokenStreamInterface $stream, bool $hasActiveGroup): bool
    {
        $token = $stream->current();
        if (null === $token) {
            return false;
        }

        if ($this->isGlueableComment($token)) {
            return true;
        }

        if (!$hasActiveGroup || $token->isComment()) {
            return false;
        }

        if ('' !== trim($token->getText())) {
            return false;
        }

        return $this->bridgesToNextGlueableComment($stream);
    }

    abstract protected function isGlueableComment(TokenInterface $token): bool;

    private function bridgesToNextGlueableComment(TokenStreamInterface $stream): bool
    {
        $current = $stream->current();
        $paragraphBreak = null !== $current && $this->hasMultipleLineBreaks($current->getText());
        $sawLineBreak = null !== $current && $this->isPureLineBreak($current->getText());
        $offset = 1;

        while (true) {
            $peeked = $stream->peek($offset);
            if (null === $peeked) {
                return false;
            }

            if ($this->isGlueableComment($peeked)) {
                return !$paragraphBreak;
            }

            if ('' !== trim($peeked->getText())) {
                return false;
            }

            if ($this->hasMultipleLineBreaks($peeked->getText())) {
                $paragraphBreak = true;
            } elseif ($this->isPureLineBreak($peeked->getText())) {
                if ($sawLineBreak) {
                    $paragraphBreak = true;
                } else {
                    $sawLineBreak = true;
                }
            }

            ++$offset;
        }
    }

    private function hasMultipleLineBreaks(string $text): bool
    {
        return substr_count($text, "\n") > 1 || substr_count($text, "\r") > 1;
    }

    private function isPureLineBreak(string $text): bool
    {
        return '' === trim($text) && 1 === preg_match('/^\r?\n$/', $text);
    }
}
