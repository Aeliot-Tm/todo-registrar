<?php

declare(strict_types=1);

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Service\File\Finder;

return (new Config())
    ->setFinder((new Finder())->in(__DIR__))
    ->setRegistrar(RegistrarType::GitHub, [
        'issue' => [
            'addTagToLabels' => true,
            'labels' => ['bug'],
        ],
        'service' => [
            'personalAccessToken' => 'test-token',
            'owner' => 'test-owner',
            'repository' => 'test-repo',
        ],
    ])
    ->setTags(['todo']);

