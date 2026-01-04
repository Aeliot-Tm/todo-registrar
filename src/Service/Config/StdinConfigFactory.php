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
use Aeliot\TodoRegistrarContracts\GeneralConfigInterface;

/**
 * @internal
 */
final readonly class StdinConfigFactory
{
    public function __construct(
        private ArrayConfigFactory $arrayConfigFactory,
        private YamlParser $yamlParser,
    ) {
    }

    public function create(): GeneralConfigInterface
    {
        $contents = file_get_contents('php://stdin');
        if (false === $contents || '' === $contents) {
            throw new UnavailableConfigException('No configuration provided via STDIN');
        }

        return $this->arrayConfigFactory->create($this->yamlParser->parse($contents));
    }
}
