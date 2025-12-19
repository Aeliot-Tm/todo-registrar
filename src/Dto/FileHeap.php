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

use Aeliot\TodoRegistrar\Service\File\Saver;

final class FileHeap
{
    private \Closure $fileUpdateCallback;
    private int $registrationCounter = 0;

    /**
     * @param \PhpToken[] $commentTokens
     * @param \PhpToken[] $tokens
     */
    public function __construct(
        private array $commentTokens,
        private array $tokens,
        \SplFileInfo $file,
        ProcessStatistic $statistic,
        Saver $saver,
    ) {
        $statistic->setFileRegistrationCount($file->getPathname(), $this->registrationCounter);
        $this->fileUpdateCallback = function () use ($file, $statistic, $saver): void {
            ++$this->registrationCounter;
            $statistic->setFileRegistrationCount($file->getPathname(), $this->registrationCounter);
            $saver->save($file, $this->tokens);
        };
    }

    /**
     * @return \PhpToken[]
     */
    public function getCommentTokens(): array
    {
        return $this->commentTokens;
    }

    public function getFileUpdateCallback(): \Closure
    {
        return $this->fileUpdateCallback;
    }

    public function getRegistrationCounter(): int
    {
        return $this->registrationCounter;
    }
}
