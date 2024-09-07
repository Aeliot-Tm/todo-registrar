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

use Aeliot\TodoRegistrar\Dto\Registrar\Todo;

interface RegistrarInterface
{
    /**
     * @deprecated skip registered by metadata {@see \Aeliot\TodoRegistrar\Dto\Tag\TagMetadata::getTicketKey() }
     */
    public function isRegistered(Todo $todo): bool;

    public function register(Todo $todo): string;
}
