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

/**
 * @internal
 */
class Detector
{
    /**
     * @param \PhpToken[] $tokens
     *
     * @return \PhpToken[]
     */
    public function filter(array $tokens): array
    {
        foreach ($tokens as $index => $token) {
            if (!$this->isComment($token)) {
                unset($tokens[$index]);
            }
        }

        return $tokens;
    }

    private function isComment(\PhpToken $token): bool
    {
        return \in_array($token->id, [\T_COMMENT, \T_DOC_COMMENT], true);
    }
}
