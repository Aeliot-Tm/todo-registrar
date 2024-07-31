<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\File;

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
