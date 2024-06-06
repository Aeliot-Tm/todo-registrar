<?php

declare(strict_types=1);

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Service\File\Finder;

return (new Config())
    ->setFinder(new Finder());
