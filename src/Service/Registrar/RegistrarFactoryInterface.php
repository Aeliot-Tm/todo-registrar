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

interface RegistrarFactoryInterface
{
    /**
     * @param array<string,mixed> $config
     */
    public function create(array $config): RegistrarInterface;
}
