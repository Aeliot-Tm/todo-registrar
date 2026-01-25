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

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Service\File\Finder;

return (new Config())
    ->setFinder((new Finder())->in(__DIR__))
    ->setRegistrar(RegistrarType::JIRA, [
        'issue' => [
            'projectKey' => 'Todo',
            'addTagToLabels' => true,
            'components' => ['Component-1', 'Component-2'],
            'labels' => ['Label-1', 'Label-2'],
            'tagPrefix' => 'tag-',
            'type' => 'Bug',
        ],
        'service' => [
            'host' => $_ENV['JIRA_HOST'],
            'personalAccessToken' => $_ENV['JIRA_PERSONAL_ACCESS_TOKEN'],
            'tokenBasedAuth' => true,
        ],
    ])
    ->setTags(['todo', 'fixme', 'a_custom_tag']);
