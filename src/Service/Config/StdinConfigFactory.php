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

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final readonly class StdinConfigFactory
{
    public function __construct(
        private ArrayConfigFactory $arrayConfigFactory,
    ) {
    }

    public function create(): Config
    {
        $contents = file_get_contents('php://stdin');
        if (false === $contents || '' === $contents) {
            throw new InvalidConfigException('No configuration provided via STDIN');
        }

        return $this->arrayConfigFactory->create(
            Yaml::parse($contents, Yaml::PARSE_CONSTANT | Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE | Yaml::PARSE_OBJECT),
        );
    }
}
