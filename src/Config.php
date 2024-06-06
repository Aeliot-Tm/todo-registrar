<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar;

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Service\File\Finder;

final class Config
{
    private Finder $finder;
    /**
     * @var array<string,mixed>
     */
    private array $registrarConfig;
    private RegistrarType $registrarType;

    public function getFinder(): Finder
    {
        return $this->finder;
    }

    public function setFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function getRegistrarConfig(): array
    {
        return $this->registrarConfig;
    }

    public function getRegistrarType(): RegistrarType
    {
        return $this->registrarType;
    }

    /**
     * @param array<string,mixed> $config
     */
    public function setRegistrar(RegistrarType $type, array $config): self
    {
        $this->registrarType = $type;
        $this->registrarConfig = $config;

        return $this;
    }
}