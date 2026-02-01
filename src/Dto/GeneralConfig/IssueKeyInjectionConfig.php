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

namespace Aeliot\TodoRegistrar\Dto\GeneralConfig;

use Aeliot\TodoRegistrar\Enum\IssueKeyPosition;
use Aeliot\TodoRegistrarContracts\IssueKeyInjectionConfigInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class IssueKeyInjectionConfig implements IssueKeyInjectionConfigInterface
{
    public const DEFAULT_ISSUE_KEY_POSITION = IssueKeyPosition::AFTER_SEPARATOR->value;
    public const DEFAULT_REPLACE_SEPARATOR = false;
    public const DEFAULT_SEPARATORS = [':', '-', '>'];

    #[Assert\Choice(
        callback: [IssueKeyPosition::class, 'getValues'],
        message: 'Option "position" must be one of: {{ choices }}'
    )]
    private ?string $position = null;

    #[Assert\Length(exactly: 1, exactMessage: 'Option "issueKeyInjection.newSeparator" must be exactly 1 character')]
    private ?string $newSeparator = null;
    private bool $replaceSeparator = self::DEFAULT_REPLACE_SEPARATOR;

    /**
     * @var string[]
     */
    #[Assert\All(constraints: [
        new Assert\Type(type: 'string', message: 'Each path in "paths.append" must be a string'),
        new Assert\Length(exactly: 1, exactMessage: 'Each summary separator must be exactly 1 character'),
    ])]
    private array $summarySeparators = self::DEFAULT_SEPARATORS;

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(IssueKeyPosition|string $position): void
    {
        $this->position = $position instanceof IssueKeyPosition ? $position->value : $position;
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

    public function getSummarySeparators(): array
    {
        return $this->summarySeparators;
    }

    /**
     * @param string[] $summarySeparators
     */
    public function setSummarySeparators(array $summarySeparators): void
    {
        $this->summarySeparators = $summarySeparators;
    }
}
