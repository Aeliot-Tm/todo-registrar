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

use Aeliot\TodoRegistrar\Enum\IssueKeyPosition;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrarContracts\FinderInterface;
use Aeliot\TodoRegistrarContracts\GeneralConfigInterface;
use Aeliot\TodoRegistrarContracts\InlineConfigFactoryInterface;
use Aeliot\TodoRegistrarContracts\InlineConfigReaderInterface;
use Aeliot\TodoRegistrarContracts\IssueKeyPositionConfigInterface;
use Aeliot\TodoRegistrarContracts\RegistrarFactoryInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback('validate')]
class Config implements GeneralConfigInterface, IssueKeyPositionConfigInterface
{
    public const DEFAULT_ISSUE_KEY_POSITION = IssueKeyPosition::AFTER_SEPARATOR->value;
    public const DEFAULT_REPLACE_SEPARATOR = false;
    public const DEFAULT_SEPARATORS = [':', '-'];
    public const DEFAULT_TAGS = ['todo', 'fixme'];

    private FinderInterface $finder;
    private ?InlineConfigFactoryInterface $InlineConfigFactory = null;
    private ?InlineConfigReaderInterface $inlineConfigReader = null;

    #[Assert\Choice(
        callback: [IssueKeyPosition::class, 'getValues'],
        message: 'Option "issueKeyPosition" must be one of: {{ choices }}'
    )]
    private string $issueKeyPosition = self::DEFAULT_ISSUE_KEY_POSITION;

    #[Assert\Length(exactly: 1, exactMessage: 'Option "newSeparator" must be exactly 1 character')]
    private ?string $newSeparator = null;
    private bool $replaceSeparator = self::DEFAULT_REPLACE_SEPARATOR;

    /**
     * @var array<string,mixed>
     */
    private array $registrarConfig;
    private RegistrarFactoryInterface|string $registrarType;

    /**
     * @var string[]
     */
    private array $summarySeparator = self::DEFAULT_SEPARATORS;

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

    public function getIssueKeyPosition(): ?string
    {
        return $this->issueKeyPosition;
    }

    public function setIssueKeyPosition(IssueKeyPosition|string $position): void
    {
        $this->issueKeyPosition = $position instanceof IssueKeyPosition ? $position->value : $position;
    }

    public function getNewSeparator(): ?string
    {
        return $this->newSeparator;
    }

    public function setNewSeparator(?string $newSeparator): void
    {
        $this->newSeparator = $newSeparator;
    }

    public function getReplaceSeparator(): bool
    {
        return $this->replaceSeparator;
    }

    public function setReplaceSeparator(bool $replaceSeparator): void
    {
        $this->replaceSeparator = $replaceSeparator;
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

    public function getSummarySeparators(): array
    {
        return $this->summarySeparator;
    }

    /**
     * @param string[] $summarySeparator
     */
    public function setSummarySeparators(array $summarySeparator): void
    {
        $this->summarySeparator = $summarySeparator;
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
