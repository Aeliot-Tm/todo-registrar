<?php

declare(strict_types=1);

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Service\File\Finder;

// Invalid: missing setRegistrar() call
return (new Config())
    ->setFinder((new Finder())->in(__DIR__));

