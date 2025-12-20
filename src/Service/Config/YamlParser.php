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

use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final readonly class YamlParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(string $content): array
    {
        $parsed = Yaml::parse(
            $content,
            Yaml::PARSE_CONSTANT | Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE | Yaml::PARSE_OBJECT,
        );

        if (!\is_array($parsed)) {
            return [];
        }

        return $this->resolveEnvVars($parsed);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function resolveEnvVars(array $config): array
    {
        array_walk_recursive($config, static function (mixed &$value): void {
            if (\is_string($value) && preg_match('/^%env\(([^)]+)\)%$/', $value, $matches)) {
                $envValue = $_ENV[$matches[1]] ?? getenv($matches[1]);
                if (false !== $envValue) {
                    $value = $envValue;
                }
            }
        });

        return $config;
    }
}
