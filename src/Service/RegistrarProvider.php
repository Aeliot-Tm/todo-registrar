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

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryRegistry;
use Aeliot\TodoRegistrarContracts\GeneralConfig\GeneralConfigInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\RegistrarFactoryInterface as LegacyRegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\RegistrarInterface as LegacyRegistrarInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
final readonly class RegistrarProvider
{
    public function __construct(
        private RegistrarFactoryRegistry $registrarFactoryRegistry,
        private ValidatorInterface $validator,
    ) {
    }

    public function getRegistrar(GeneralConfigInterface $config): RegistrarInterface|LegacyRegistrarInterface
    {
        $registrarType = $config->getRegistrarType();

        if ($registrarType instanceof RegistrarFactoryInterface || $registrarType instanceof LegacyRegistrarFactoryInterface) {
            $registrarFactory = $registrarType;
        } elseif (class_exists($registrarType)
            && (
                is_a($registrarType, RegistrarFactoryInterface::class, true)
                || is_a($registrarType, LegacyRegistrarFactoryInterface::class, true)
            )
        ) {
            $registrarFactory = new $registrarType();
        } else {
            $registrarFactory = $this->getByEnumValue($registrarType);
        }

        return $registrarFactory->create($config->getRegistrarConfig(), $this->validator);
    }

    private function getByEnumValue(string $registrarType): RegistrarFactoryInterface|LegacyRegistrarFactoryInterface
    {
        // add some backward compatibility
        if ('github' === strtolower($registrarType)) {
            $registrarType = RegistrarType::GitHub->value;
        }
        $transformedType = RegistrarType::tryFrom($registrarType);
        if (!$transformedType) {
            throw new InvalidConfigException(\sprintf('Invalid type of registrar: %s', $registrarType));
        }

        return $this->registrarFactoryRegistry->getFactory($transformedType);
    }
}
