<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Comment;

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
