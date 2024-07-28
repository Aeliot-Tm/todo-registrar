<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar;

interface RegistrarFactoryInterface
{
    /**
     * @param array<string,mixed> $config
     */
    public function create(array $config): RegistrarInterface;
}
