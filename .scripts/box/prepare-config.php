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

$configPath = __DIR__ . '/config.json';
$config = json_decode(file_get_contents($configPath), true, 512, \JSON_THROW_ON_ERROR);

$config['base-path'] = dirname($configPath, 3) . '/';

file_put_contents($configPath, json_encode($config, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT));
