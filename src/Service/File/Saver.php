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

use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;

/**
 * @internal
 */
final readonly class Saver
{
    /**
     * @param \Traversable<TokenInterface> $tokens
     */
    public function save(\SplFileInfo $file, \Traversable $tokens): void
    {
        file_put_contents($file->getPathname(), implode('', array_map(
            static fn (TokenInterface $x): string => $x->getText(),
            iterator_to_array($tokens)),
        ));
    }
}
