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

namespace Aeliot\TodoRegistrar;

use Aeliot\TodoRegistrar\Contracts\GeneralConfigInterface;
use Aeliot\TodoRegistrar\Contracts\InlineConfigFactoryInterface;
use Aeliot\TodoRegistrar\Contracts\InlineConfigReaderInterface;
use Aeliot\TodoRegistrar\Contracts\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Service\File\Finder;

class Config implements GeneralConfigInterface
{
    private Finder $finder;
    private ?InlineConfigFactoryInterface $InlineConfigFactory = null;
    private ?InlineConfigReaderInterface $inlineConfigReader = null;
    /**
     * @var array<string,mixed>
     */
    private array $registrarConfig;
    private RegistrarType|RegistrarFactoryInterface|string $registrarType;
    /**
     * @var string[]
     */
    private array $tags = ['todo', 'fixme'];

    public function getFinder(): Finder
    {
        return $this->finder;
    }

    public function setFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    public function getInlineConfigFactory(): ?InlineConfigFactoryInterface
    {
        return $this->InlineConfigFactory;
    }

    public function setInlineConfigFactory(?InlineConfigFactoryInterface $InlineConfigFactory): void
    {
        $this->InlineConfigFactory = $InlineConfigFactory;
    }

    public function getInlineConfigReader(): ?InlineConfigReaderInterface
    {
        return $this->inlineConfigReader;
    }

    public function setInlineConfigReader(?InlineConfigReaderInterface $inlineConfigReader): void
    {
        $this->inlineConfigReader = $inlineConfigReader;
    }

    public function getRegistrarConfig(): array
    {
        return $this->registrarConfig;
    }

    public function getRegistrarType(): RegistrarType|RegistrarFactoryInterface|string
    {
        return $this->registrarType;
    }

    /**
     * @param array<string,mixed> $config
     */
    public function setRegistrar(RegistrarType|RegistrarFactoryInterface|string $type, array $config): self
    {
        $this->registrarType = $type;
        $this->registrarConfig = $config;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }
}
