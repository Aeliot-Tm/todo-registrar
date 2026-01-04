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

use Aeliot\EnvResolver\Service\StringProcessor;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final readonly class YamlParser
{
    public function __construct(private StringProcessor $stringProcessor)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function parse(string $content): array
    {
        $content = $this->stringProcessor->process($content);
        try {
            $parsed = Yaml::parse(
                $content,
                Yaml::PARSE_CONSTANT | Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE | Yaml::PARSE_OBJECT,
            );
            if (!\is_array($parsed)) {
                throw new \UnexpectedValueException('Invalid YAML string');
            }
        } catch (\Exception $e) {
            throw new InvalidConfigException('Cannot parse YAML', 0, $e);
        }

        return $parsed;
    }
}
