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

namespace Aeliot\TodoRegistrar\Service\File;

/**
 * @internal
 */
class Tokenizer
{
    /**
     * @return \PhpToken[]
     */
    public function tokenize(\SplFileInfo $file): array
    {
        $pathname = $file->getPathname();
        $contents = file_get_contents($pathname);
        if (false === $contents) {
            throw new \RuntimeException(\sprintf('Cannot read file %s', $pathname));
        }

        return \PhpToken::tokenize($contents);
    }
}
