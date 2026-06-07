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

use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\Todo\TodoInterface;

/**
 * @internal
 */
final class DryRunRegistrar implements RegistrarInterface
{
    private int $counter = 0;

    public function register(TodoInterface $todo): string
    {
        return '#dry-run-' . ++$this->counter;
    }
}
