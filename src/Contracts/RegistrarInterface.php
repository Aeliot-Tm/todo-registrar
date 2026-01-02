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

use Aeliot\TodoRegistrarContracts\RegistrarInterface as RegistrarContractInterface;

/**
 * @deprecated use {@see RegistrarContractInterface }
 */
interface RegistrarInterface
{
    /**
     * @return string Key of registered ticket
     */
    public function register(TodoInterface $todo): string;
}

trigger_deprecation(
    'aeliot/todo-registrar',
    '2.3.0',
    'Use interfaces from "aeliot/todo-registrar-contracts". Interface %s will be removed in version 3.0.0.',
    RegistrarInterface::class,
);
