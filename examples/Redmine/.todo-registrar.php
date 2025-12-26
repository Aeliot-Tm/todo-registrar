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
    ->setRegistrar(RegistrarType::Redmine, [
        'issue' => [
            'addTagToLabels' => false,
            'labels' => [],
            'tagPrefix' => 'tag-',
            'summaryPrefix' => '[TODO] ',
            'assignee' => null,  # username, login, email, or user ID
            'tracker' => 'Bugs',  # tracker name or ID
            'priority' => 'Low',  # priority name ('High') or ID
            'category' => 'Categ B',  # category name or ID
            'fixed_version' => null,  # version name or ID
            'start_date' => null,
            'due_date' => null,
            'estimated_hours' => null,
        ],
        'project' => 'testing-project',  # Project identifier or ID
        'service' => [
            // https://redmine.example.com
            // For local Redmine in Docker: use 'http://host.docker.internal:3000' instead of 'http://localhost:3000'
            'url' => $_ENV['REDMINE_URL'] ?? null,
            'apikeyOrUsername' => $_ENV['REDMINE_API_KEY'] ?? $_ENV['REDMINE_USERNAME'] ?? null,
            // If password is provided, Basic Auth will be used (username:password)
            // Otherwise, apikeyOrUsername will be treated as API key
            'password' => $_ENV['REDMINE_PASSWORD'] ?? null,
        ],
    ])
    ->setTags(['todo', 'fixme', 'a_custom_tag']);
