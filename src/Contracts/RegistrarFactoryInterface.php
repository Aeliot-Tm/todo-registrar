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

namespace Aeliot\TodoRegistrar\Contracts;

use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrarContracts\RegistrarFactoryInterface as RegistrarFactoryContractInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @deprecated use {@see RegistrarFactoryContractInterface }. Note the second parameter `$validator` is removed
 *             in new interface but will be passed implicitly. Main purpose is to not enforce using of it
 *             and to reduce dependencies.
 */
#[AutoconfigureTag('aeliot.todo_registrar.registrar_factory')]
interface RegistrarFactoryInterface
{
    /**
     * @param array<string,mixed> $config
     * @param ValidatorInterface|null $validator validator instance (optional for backward compatibility)
     *
     * @throws ConfigValidationException
     */
    public function create(array $config, ?ValidatorInterface $validator = null): RegistrarInterface;
}

trigger_deprecation(
    'aeliot/todo-registrar',
    '2.3.0',
    'Use interfaces from "aeliot/todo-registrar-contracts". Interface %s will be removed in version 3.0.0.',
    RegistrarFactoryInterface::class,
);
