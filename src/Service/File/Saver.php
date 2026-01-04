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
final readonly class Saver
{
    /**
     * @param \PhpToken[] $tokens
     */
    public function save(\SplFileInfo $file, array $tokens): void
    {
        $content = implode('', array_map(static fn (\PhpToken $x): string => $x->text, $tokens));
        file_put_contents($file->getPathname(), $content);
    }
}
