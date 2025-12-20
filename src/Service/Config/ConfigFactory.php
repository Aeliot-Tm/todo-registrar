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
use Aeliot\TodoRegistrar\Contracts\GeneralConfigInterface;

/**
 * @internal
 */
final readonly class ConfigFactory
{
    public function __construct(
        private ArrayConfigFactory $arrayConfigFactory,
        private YamlParser $yamlParser,
    ) {
    }

    public function create(string $path): GeneralConfigInterface
    {
        return match (strtolower(pathinfo($path, \PATHINFO_EXTENSION))) {
            'yaml', 'yml' => $this->getFromYAML($path),
            default => throw new \DomainException('Unsupported type of config')
        };
    }

    private function getFromYAML(string $path): Config
    {
        $contents = file_get_contents($path);
        if (false === $contents) {
            throw new \RuntimeException(\sprintf('Config file "%s" is not readable', $path));
        }

        return $this->arrayConfigFactory->create($this->yamlParser->parse($contents));
    }
}
