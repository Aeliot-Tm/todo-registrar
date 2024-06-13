<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar;

use Aeliot\TodoRegistrar\Dto\Registrar\Todo;

interface RegistrarInterface
{
    public function isRegistered(Todo $todo): bool;

    public function register(Todo $todo): string;
}