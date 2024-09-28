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

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;

return static function (Configuration $config): Configuration {
    // add packages used by knplabs/github-api implicitly
    $config->addNamedFilter(NamedFilter::fromString('guzzlehttp/guzzle'));
    $config->addNamedFilter(NamedFilter::fromString('http-interop/http-factory-guzzle'));

    return $config;
};
