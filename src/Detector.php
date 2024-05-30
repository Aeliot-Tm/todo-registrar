<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar;

final class Detector
{
    /**
     * @param \PhpToken[] $tokens
     *
     * @return \PhpToken[]
     */
    public function filter(array $tokens): array
    {
        foreach ($tokens as $index => $token) {
            if ($token->id !== T_COMMENT) {
                unset($tokens[$index]);
            }
        }

        return array_values($tokens);
    }
}