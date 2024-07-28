<?php

declare(strict_types=1);

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
