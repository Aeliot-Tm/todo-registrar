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

namespace Aeliot\TodoRegistrar\Service\Registrar\DryRun;

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * @internal
 */
#[AsTaggedItem(index: RegistrarType::DryRun->value)]
final readonly class DryRunRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config): RegistrarInterface
    {
        return new DryRunRegistrar();
    }
}
