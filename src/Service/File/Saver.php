<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\File;

final class Saver
{
    /**
     * @param \PhpToken[] $tokens
     */
    public function save(\SplFileInfo $file, array $tokens): void
    {
        $content = implode('', array_map(static fn(\PhpToken $x): string => $x->text, $tokens));
        file_put_contents($file->getPathname(), $content);
    }
}