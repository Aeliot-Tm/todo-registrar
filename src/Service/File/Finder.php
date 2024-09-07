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

use Symfony\Component\Finder\Finder as SymfonyFinder;

class Finder extends SymfonyFinder
{
    public function __construct()
    {
        parent::__construct();

        $this
            ->files()
            ->name('/\.php$/')
            ->exclude('vendor');
    }
}
