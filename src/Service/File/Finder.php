<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\File;

use Symfony\Component\Finder\Finder as SymfonyFinder;

final class Finder extends SymfonyFinder
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
