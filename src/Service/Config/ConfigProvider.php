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

use Aeliot\TodoRegistrar\Contracts\GeneralConfigInterface;

/**
 * @internal
 */
final readonly class ConfigProvider
{
    public function __construct(
        private ConfigFileDetector $configFileDetector,
        private ConfigFactory $configFactory,
    ) {
    }

    public function getConfig(?string $path): GeneralConfigInterface
    {
        $path = $this->configFileDetector->getPath($path);
        if ('php' === strtolower(pathinfo($path, \PATHINFO_EXTENSION))) {
            return require $path;
        }

        return $this->configFactory->create($path);
    }
}
