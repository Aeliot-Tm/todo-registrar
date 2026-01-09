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

namespace Aeliot\TodoRegistrar\Service\Registrar;

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrarContracts\RegistrarFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @internal
 */
final readonly class RegistrarFactoryRegistry
{
    /**
     * @param ServiceLocator<RegistrarFactoryInterface> $registrarFactoryLocator
     */
    public function __construct(
        #[AutowireLocator('aeliot.todo_registrar.registrar_factory')]
        private ServiceLocator $registrarFactoryLocator,
    ) {
    }

    public function getFactory(RegistrarType $type): RegistrarFactoryInterface
    {
        if (!$this->registrarFactoryLocator->has($type->value)) {
            throw new InvalidConfigException(\sprintf('Not supported registrar type "%s"', $type->value));
        }

        return $this->registrarFactoryLocator->get($type->value);
    }
}
