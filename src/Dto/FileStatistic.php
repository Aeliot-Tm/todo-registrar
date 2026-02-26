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

namespace Aeliot\TodoRegistrar\Dto;

/**
 * @internal
 */
final readonly class FileStatistic
{
    public function __construct(
        private string $path,
        private ProcessStatistic $statistic,
    ) {
        $statistic->markFileVisit($path);
    }

    public function getRegistrationCount(): int
    {
        return $this->statistic->getFileRegistrationCount($this->path);
    }

    public function tickRegistration(): void
    {
        $this->statistic->tickRegistration($this->path);
    }
}
