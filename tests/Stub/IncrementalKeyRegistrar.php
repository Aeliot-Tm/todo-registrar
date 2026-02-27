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

namespace Aeliot\TodoRegistrar\Test\Stub;

use Aeliot\TodoRegistrarContracts\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\TodoInterface;

/**
 * Stub registrar for testing purposes.
 * Does not make any API calls, returns an incrementing ticket key on each registration.
 */
final class IncrementalKeyRegistrar implements RegistrarInterface
{
    private int $counter = 0;

    public function __construct(private string $prefix = 'KEY')
    {
    }

    public function register(TodoInterface $todo): string
    {
        return $this->prefix . '-' . ++$this->counter;
    }
}
