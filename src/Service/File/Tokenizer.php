<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\File;

final class Tokenizer
{
    /**
     * @return \PhpToken[]
     */
    public function tokenize(\SplFileInfo $file): array
    {
        return \PhpToken::tokenize(file_get_contents($file->getPathname()));
    }
}