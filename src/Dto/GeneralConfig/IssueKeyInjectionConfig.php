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

final class IssueKeyInjectionConfig implements IssueKeyInjectionConfigInterface
{
    public const DEFAULT_ISSUE_KEY_POSITION = IssueKeyPosition::AFTER_SEPARATOR->value;
    public const DEFAULT_REPLACE_SEPARATOR = false;
    public const DEFAULT_SEPARATORS = [':', '-', '>'];

    private ?string $issueKeyPosition = null;
    private ?string $newSeparator = null;
    private bool $replaceSeparator = self::DEFAULT_REPLACE_SEPARATOR;

    /**
     * @var string[]
     */
    private array $summarySeparators = self::DEFAULT_SEPARATORS;

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
