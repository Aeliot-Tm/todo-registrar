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
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\File\Finder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
final readonly class ArrayConfigFactory
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @param array<string,mixed> $options
     */
    public function create(array $options): Config
    {
        $arrayConfig = $this->createArrayConfig($options);

        $pathsConfig = $arrayConfig->getPaths();
        $registrarConfig = $arrayConfig->getRegistrar();

        $config = (new Config())
            ->setFinder($this->createFinder($pathsConfig))
            ->setRegistrar($registrarConfig->getType(), $registrarConfig->getOptions() ?? []);

        $tags = $arrayConfig->getTags();
        if ($tags) {
            $config->setTags($tags);
        }

        return $config;
    }

    /**
     * @param array<string,mixed> $options
     */
    private function createArrayConfig(array $options): ArrayConfig
    {
        $arrayConfig = new ArrayConfig($options);
        $violations = $this->validator->validate($arrayConfig);

        if (\count($violations) > 0) {
            throw new ConfigValidationException($violations, 'general');
        }

        return $arrayConfig;
    }

    private function createFinder(?PathsConfig $pathsConfig): Finder
    {
        $finder = (new Finder())
            ->files()
            ->ignoreVCS(true)
            ->in($pathsConfig?->getIn() ?? (getcwd() ?: '.'));

        $append = $pathsConfig?->getAppend();
        if ($append) {
            $finder->append((array) $append);
        }

        $exclude = $pathsConfig?->getExclude();
        if ($exclude) {
            $finder->exclude((array) $exclude);
        }

        return $finder;
    }
}
