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
final readonly class FinderNamePatternBuilder
{
    /**
     * @param string[] $extensions
     */
    public function buildFromExtensions(array $extensions): string
    {
        $quoted = array_map(static fn (string $extension): string => preg_quote($extension, '/'), $extensions);

        return '/\.(?:' . implode('|', $quoted) . ')$/';
    }
}
