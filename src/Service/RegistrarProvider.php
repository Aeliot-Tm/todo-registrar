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
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryRegistry;
use Aeliot\TodoRegistrarContracts\Exception\InvalidConfigException as InvalidConfigExceptionInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;
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

    /**
     * @param RegistrarFactoryInterface|class-string<RegistrarFactoryInterface>|string $registrarType
     * @param array<string,mixed> $registrarConfig
     *
     * @throws ConfigValidationException
     * @throws InvalidConfigException
     * @throws InvalidConfigExceptionInterface
     */
    public function getRegistrar(
        RegistrarFactoryInterface|string $registrarType,
        array $registrarConfig,
    ): RegistrarInterface {
        if ($registrarType instanceof RegistrarFactoryInterface) {
            $registrarFactory = $registrarType;
        } elseif (class_exists($registrarType) && is_a($registrarType, RegistrarFactoryInterface::class, true)) {
            $registrarFactory = new $registrarType();
        } else {
            $registrarFactory = $this->getByEnumValue($registrarType);
        }

        // @phpstan-ignore-next-line
        return $registrarFactory->create($registrarConfig, $this->validator);
    }

    /**
     * @throws InvalidConfigException
     */
    private function getByEnumValue(string $registrarType): RegistrarFactoryInterface
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
