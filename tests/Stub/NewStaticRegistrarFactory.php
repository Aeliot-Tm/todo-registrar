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

use Aeliot\TodoRegistrarContracts\Registrar\RegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;

/**
 * Stub registrar factory for testing purposes (new contracts interfaces).
 * Creates NewStaticKeyRegistrar instances without making any API calls.
 */
final class NewStaticRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config): RegistrarInterface
    {
        return new NewStaticKeyRegistrar($config['ticket_key']);
    }
}
