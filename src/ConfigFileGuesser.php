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
final class ConfigFileGuesser
{
    public function __construct(private AbsolutePathMaker $absolutePathMaker)
    {
    }

    public function guess(): string
    {
        $candidates = [
            '.todo-registrar.php',
            '.todo-registrar.dist.php',
        ];
        foreach ($candidates as $candidate) {
            $path = $this->absolutePathMaker->prepare($candidate);
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \DomainException('Cannot detect default config file');
    }
}
