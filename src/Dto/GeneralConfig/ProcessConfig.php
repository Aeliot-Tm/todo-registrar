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

use Aeliot\TodoRegistrarContracts\GeneralConfig\ProcessConfigInterface;

final class ProcessConfig implements ProcessConfigInterface
{
    public const DEFAULT_GLUE_SAME_TICKETS = false;
    public const DEFAULT_GLUE_SEQUENTIAL_COMMENTS = false;

    /**
     * @var array<string, string>
     */
    private array $extensionAliases = [];

    private bool $glueSameTickets = self::DEFAULT_GLUE_SAME_TICKETS;
    private bool $glueSequentialComments = self::DEFAULT_GLUE_SEQUENTIAL_COMMENTS;

    /**
     * @return array<string, string>
     */
    public function getExtensionAliases(): array
    {
        return $this->extensionAliases;
    }

    /**
     * @param array<string, string> $extensionAliases
     */
    public function setExtensionAliases(array $extensionAliases): void
    {
        $this->extensionAliases = $extensionAliases;
    }

    public function isGlueSameTicket(): bool
    {
        return $this->glueSameTickets;
    }

    public function setGlueSameTickets(bool $glueSameTickets): void
    {
        $this->glueSameTickets = $glueSameTickets;
    }

    public function isGlueSequentialComments(): bool
    {
        return $this->glueSequentialComments;
    }

    public function setGlueSequentialComments(bool $glueSequentialComments): void
    {
        $this->glueSequentialComments = $glueSequentialComments;
    }
}
