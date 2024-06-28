<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar;

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Service\File\Finder;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryInterface;

class Config
{
    private Finder $finder;
    private ?InlineConfigReaderInterface $inlineConfigReader = null;
    /**
     * @var array<string,mixed>
     */
    private array $registrarConfig;
    private RegistrarType|RegistrarFactoryInterface $registrarType;
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

    /**
     * @return array<string,mixed>
     */
    public function getRegistrarConfig(): array
    {
        return $this->registrarConfig;
    }

    public function getRegistrarType(): RegistrarType|RegistrarFactoryInterface
    {
        return $this->registrarType;
    }

    /**
     * @param array<string,mixed> $config
     */
    public function setRegistrar(RegistrarType|RegistrarFactoryInterface $type, array $config): self
    {
        $this->registrarType = $type;
        $this->registrarConfig = $config;

        return $this;
    }

    /**
     * @return string[]
     */
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

    public function getInlineConfigReader(): ?InlineConfigReaderInterface
    {
        return $this->inlineConfigReader;
    }

    public function setInlineConfigReader(?InlineConfigReaderInterface $inlineConfigReader): void
    {
        $this->inlineConfigReader = $inlineConfigReader;
    }
}
