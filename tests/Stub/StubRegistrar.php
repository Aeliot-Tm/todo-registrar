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
 * Does not make any API calls, returns ticket key from config.
 */
final class StubRegistrar implements RegistrarInterface
{
    public function __construct(
        private string $ticketKey,
    ) {
    }

    public function register(TodoInterface $todo): string
    {
        return $this->ticketKey;
    }
}
