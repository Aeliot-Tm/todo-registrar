<?php

declare(strict_types=1);

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Service\File\Finder;

return (new Config())
    ->setFinder(
        (new Finder())
            ->in(dirname(__DIR__, 2))
            ->exclude(['vendor', 'var', 'tests'])
    )
    ->setRegistrar(RegistrarType::YandexTracker, [
        'queue' => 'MYQUEUE',
        'issue' => [
            'addTagToLabels' => true,
            'assignee' => 'developer.login',
            'labels' => ['tech-debt', 'from-code'],
            'priority' => 'normal',
            'tagPrefix' => '',
            'type' => 'task',
        ],
        'service' => [
            'token' => $_ENV['YANDEX_TRACKER_TOKEN'] ?? null,
            'orgId' => $_ENV['YANDEX_TRACKER_ORG_ID'] ?? null,
        ],
    ])
    ->setTags(['todo', 'fixme']);

