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

$finder = (new Finder())
    ->files()
    ->ignoreVCS(true)
    ->in(dirname(__DIR__, 2))
    ->exclude(['tests/fixtures', 'var', 'vendor'])
    ->append([dirname(__DIR__, 2) . '/bin/todo-registrar']);

return (new Config())
    ->setFinder($finder)
    ->setRegistrar(RegistrarType::GitHub, [
        'issue' => [
            'addTagToLabels' => true,
            'labels' => ['tech-debt'],
            'tagPrefix' => 'tag-',
        ],
        'service' => [
            'personalAccessToken' => $_ENV['GH_PERSONAL_ACCESS_TOKEN'] ?? $_SERVER['GH_PERSONAL_ACCESS_TOKEN'] ?? null,
            'owner' => $_ENV['GITHUB_REPOSITORY_OWNER'] ?? $_SERVER['GITHUB_REPOSITORY_OWNER'] ?? null,
            'repository' => substr($_ENV['GITHUB_REPOSITORY'] ?? $_SERVER['GITHUB_REPOSITORY'] ?? '',
                strlen($_ENV['GITHUB_REPOSITORY_OWNER'] ?? $_SERVER['GITHUB_REPOSITORY_OWNER'] ?? '') + 1),
        ],
    ])
    ->setTags(['todo', 'fixme']);
