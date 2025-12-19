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

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

// TODO #129 add factory of different registrars
#[AutoconfigureTag('aeliot.todo_registrar.registrar_factory')]
interface RegistrarFactoryInterface
{
    /**
     * @param array<string,mixed> $config
     */
    public function create(array $config): RegistrarInterface;
}
