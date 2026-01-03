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
final class ProcessStatistic
{
    /**
     * @var array<string,int>
     */
    private array $updatedFiles = [];

    public function setFileRegistrationCount(string $path, int $countNewTodos): void
    {
        $this->updatedFiles[$path] = $countNewTodos;
    }

    public function getFileRegistrationCount(string $path): int
    {
        return $this->updatedFiles[$path];
    }

    public function getCountUpdatedFiles(): int
    {
        return \count(array_filter($this->updatedFiles));
    }

    public function getCountRegisteredTODOs(): int
    {
        return (int) array_sum($this->updatedFiles);
    }
}
