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

namespace Aeliot\TodoRegistrar\Service\Config;

use Aeliot\TodoRegistrar\Exception\UnavailableConfigException;

/**
 * @internal
 */
final readonly class ConfigFileDetector
{
    public function __construct(
        private AbsolutePathMaker $absolutePathMaker,
        private ConfigFileGuesser $configFileGuesser,
    ) {
    }

    public function getPath(?string $path): string
    {
        if ($path) {
            $path = $this->absolutePathMaker->prepare($path);
        }
        $path ??= $this->configFileGuesser->guess();

        if (!file_exists($path)) {
            throw new UnavailableConfigException(\sprintf('Config file "%s" does not exist', $path));
        }

        return $path;
    }
}
