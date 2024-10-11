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

namespace Aeliot\TodoRegistrar;

use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final class ConfigFactory
{
    public function __construct(private ArrayConfigFactory $arrayConfigFactory)
    {
    }

    public function create(string $path): Config
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(\sprintf('Config file "%s" does not exist', $path));
        }

        return match (strtolower(pathinfo($path, \PATHINFO_EXTENSION))) {
            'php' => $this->getFromPHP($path),
            'yaml', 'yml' => $this->getFromYAML($path),
            default => throw new \DomainException('Unsupported type of config')
        };
    }

    private function getFromPHP(string $path): Config
    {
        return require $path;
    }

    private function getFromYAML(string $path): Config
    {
        $contents = file_get_contents($path);
        if (false === $contents) {
            throw new \RuntimeException(\sprintf('Config file "%s" does not exist', $path));
        }

        return $this->arrayConfigFactory->create(Yaml::parse(
            $contents,
            Yaml::PARSE_CONSTANT | Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE | Yaml::PARSE_OBJECT,
        ));
    }
}
