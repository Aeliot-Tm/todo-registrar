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

namespace Aeliot\TodoRegistrar;

/**
 * @internal
 */
final class AbsolutePathMaker
{
    public function prepare(string $path): string
    {
        if (preg_match('#^(?:[[:alpha:]]:[/\\\\]|/)#', $path)) {
            return $path;
        }

        return getcwd() . '/' . $path;
    }
}
