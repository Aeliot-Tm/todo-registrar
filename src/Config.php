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

use Aeliot\TodoRegistrar\Dto\GeneralConfig\IssueKeyInjectionConfig;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrarContracts\FinderInterface;
use Aeliot\TodoRegistrarContracts\GeneralConfigInterface;
use Aeliot\TodoRegistrarContracts\InlineConfigFactoryInterface;
use Aeliot\TodoRegistrarContracts\InlineConfigReaderInterface;
use Aeliot\TodoRegistrarContracts\IssueKeyInjectionAwareGeneralConfigInterface;
use Aeliot\TodoRegistrarContracts\IssueKeyInjectionConfigInterface;
use Aeliot\TodoRegistrarContracts\RegistrarFactoryInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Config implements GeneralConfigInterface, IssueKeyInjectionAwareGeneralConfigInterface
{
    public const DEFAULT_TAGS = ['todo', 'fixme'];

    private FinderInterface $finder;
    private ?InlineConfigFactoryInterface $InlineConfigFactory = null;
    private ?InlineConfigReaderInterface $inlineConfigReader = null;
    #[Assert\Valid]
    private ?IssueKeyInjectionConfig $issueKeyInjectionConfig = null;

    /**
     * NOTE: It has to be validated by registrar factory.
     *       Symfony Validator passed for that purpose.
     *       Even it is not described in interface (to keep it as simple as possible).
     *
     * @see \Aeliot\TodoRegistrar\Service\RegistrarProvider::getRegistrar()
     *
     * @var array<string,mixed>
     */
    private array $registrarConfig;
    private RegistrarFactoryInterface|string $registrarType;

    /**
     * @var string[]
     */
    private array $tags = self::DEFAULT_TAGS;

    public function getFinder(): FinderInterface
    {
        return $this->finder;
    }

    public function setFinder(FinderInterface $finder): self
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

    public function getIssueKeyInjectionConfig(): ?IssueKeyInjectionConfigInterface
    {
        return $this->issueKeyInjectionConfig;
    }

    public function setIssueKeyInjectionConfig(IssueKeyInjectionConfig $config): self
    {
        $this->issueKeyInjectionConfig = $config;

        return $this;
    }

    public function getRegistrarConfig(): array
    {
        return $this->registrarConfig;
    }

    public function getRegistrarType(): RegistrarFactoryInterface|string
    {
        return $this->registrarType;
    }

    /**
     * @param array<string,mixed> $config
     */
    public function setRegistrar(RegistrarType|RegistrarFactoryInterface|string $type, array $config): self
    {
        if ($type instanceof RegistrarType) {
            $type = $type->value;
        }

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

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        $reflection = new \ReflectionClass($this);

        if (!$reflection->getProperty('finder')->isInitialized($this)) {
            $context->buildViolation('Option "finder" is required. Call setFinder() method.')
                ->atPath('finder')
                ->addViolation();
        }

        if (!$reflection->getProperty('registrarType')->isInitialized($this)) {
            $context->buildViolation('Option "registrar" is required. Call setRegistrar() method.')
                ->atPath('registrar')
                ->addViolation();
        }
    }
}
