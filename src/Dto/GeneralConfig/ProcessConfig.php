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

use Aeliot\TodoRegistrarContracts\ProcessConfigInterface;

final class ProcessConfig implements ProcessConfigInterface
{
    public const DEFAULT_GLUE_SEQUENTIAL_COMMENTS = false;

    private bool $glueSequentialComments = self::DEFAULT_GLUE_SEQUENTIAL_COMMENTS;

    public function isGlueSequentialComments(): bool
    {
        return $this->glueSequentialComments;
    }

    public function setGlueSequentialComments(bool $glueSequentialComments): void
    {
        $this->glueSequentialComments = $glueSequentialComments;
    }
}
