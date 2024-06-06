<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar;

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Service\Registrar\JIRA\JiraRegistrarFactory;

final class RegistrarFactory
{
    /**
     * @param array<string,mixed> $config
     */
    public function createRegistrar(RegistrarType $type, array $config): RegistrarInterface
    {
        return $this->getExactFactory($type)->create($config);
    }

    private function getExactFactory(RegistrarType $type): RegistrarFactoryInterface
    {
        return match ($type) {
            RegistrarType::JIRA => new JiraRegistrarFactory(),
            // TODO add factory of different registrars
            default => throw new \DomainException(sprintf('Not supported registrar type "%s"', $type->value)),
        };
    }
}