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
    ->setFinder((new Finder())->in(dirname(__DIR__, 2).'/src/Service/Registrar/GitLab'))
    ->setRegistrar(RegistrarType::GitLab, [
        'issue' => [
            // Use either <project id> (more efficient) or <project path> (mode readable)
            'project' => $_ENV['GITLAB_PROJECT_IDENTIFIER'],
            'addTagToLabels' => true,
            'assignees' => ['username1', 'username2'],
            'labels' => ['tech-debt', 'Label-2'],
            'tagPrefix' => 'tag-',
            'milestone' => 123, // optional
            'due_date' => '2025-12-31', // optional, format: YYYY-MM-DD
        ],
        'service' => [
            'personalAccessToken' => $_ENV['GITLAB_PERSONAL_ACCESS_TOKEN'],
            // Or define oauthToken for OAuth type of authentication
            // 'oauthToken' => $_ENV['GITLAB_OAUTH_TOKEN'] ?? null,
            'host' => $_ENV['GITLAB_HOST'] ?? 'https://gitlab.com', // optional, for self-hosted
        ],
    ])
    ->setTags(['todo', 'fixme', 'a_custom_tag']);

