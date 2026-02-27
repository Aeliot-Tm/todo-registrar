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
    private int $countCommentTokens = 0;
    private int $countGluedTodos = 0;
    private int $countIgnoredTodos = 0;

    /**
     * @var array<string,int>
     */
    private array $files = [];

    public function getCountAnalyzedFiles(): int
    {
        return \count($this->files);
    }

    public function getCountCommentTokens(): int
    {
        return $this->countCommentTokens;
    }

    public function getCountGluedTodos(): int
    {
        return $this->countGluedTodos;
    }

    public function getCountIgnoredTodos(): int
    {
        return $this->countIgnoredTodos;
    }

    public function getCountUpdatedFiles(): int
    {
        return \count(array_filter($this->files));
    }

    public function getCountRegisteredTODOs(): int
    {
        return (int) array_sum($this->files);
    }

    public function getFileRegistrationCount(string $path): int
    {
        return $this->files[$path];
    }

    public function getTodosTotal(): int
    {
        return $this->getCountRegisteredTODOs() + $this->countGluedTodos + $this->countIgnoredTodos;
    }

    public function markFileVisit(string $path): void
    {
        $this->files[$path] ??= 0;
    }

    public function tickCommentToken(): void
    {
        ++$this->countCommentTokens;
    }

    public function tickGluedTodo(): void
    {
        ++$this->countGluedTodos;
    }

    public function tickIgnoredTodo(): void
    {
        ++$this->countIgnoredTodos;
    }

    public function tickRegistration(string $path): void
    {
        ++$this->files[$path];
    }
}
