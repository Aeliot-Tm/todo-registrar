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

use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrarContracts\GeneralConfigInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
final readonly class ConfigProvider
{
    public function __construct(
        private ConfigFileDetector $configFileDetector,
        private ConfigFactory $configFactory,
        private StdinConfigFactory $stdinConfigFactory,
        private ValidatorInterface $validator,
    ) {
    }

    public function getConfig(?string $path): GeneralConfigInterface
    {
        if ('STDIN' === strtoupper((string) $path)) {
            return $this->stdinConfigFactory->create();
        }

        $path = $this->configFileDetector->getPath($path);
        if ('php' === strtolower(pathinfo($path, \PATHINFO_EXTENSION))) {
            return $this->loadPhpConfig($path);
        }

        return $this->configFactory->create($path);
    }

    private function loadPhpConfig(string $path): GeneralConfigInterface
    {
        $config = require $path;
        if (!$config instanceof GeneralConfigInterface) {
            throw new InvalidConfigException(\sprintf('PHP config file "%s" must return an instance of %s, got %s', $path, GeneralConfigInterface::class, get_debug_type($config)));
        }

        $violations = $this->validator->validate($config);
        if (\count($violations) > 0) {
            throw new ConfigValidationException($violations, 'Invalid PHP config');
        }

        return $config;
    }
}
