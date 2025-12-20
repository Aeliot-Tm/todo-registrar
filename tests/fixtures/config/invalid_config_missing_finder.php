<?php

declare(strict_types=1);

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Enum\RegistrarType;

// Invalid: missing setFinder() call
return (new Config())
    ->setRegistrar(RegistrarType::GitHub, [
        'service' => ['personalAccessToken' => 'test'],
    ]);

