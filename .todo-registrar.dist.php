<?php

declare(strict_types=1);

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Service\File\Finder;

return (new Config())
    ->setFinder((new Finder())->in(__DIR__))
    ->setRegistrar(RegistrarType::JIRA, [
        'issue' => [
            'addTagToLabels' => true,
            'components' => ['Component-1', 'Component-2'],
            'labels' => ['Label-1', 'Label-2'],
            'tagPrefix' => 'tag-',
            'type' => 'Bug',
        ],
        'projectKey' => 'Todo',
        'service' => [
            'host' => $_ENV['JIRA_HOST'] ?? 'localhost',
            'personalAccessToken' => $_ENV['JIRA_PERSONAL_ACCESS_TOKEN'] ?? null,
            'tokenBasedAuth' => true,
        ]
    ])
    ->setTags(['todo', 'fixme', 'a_custom_tag']);
