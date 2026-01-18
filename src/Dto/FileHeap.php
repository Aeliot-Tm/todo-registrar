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

use Aeliot\TodoRegistrar\Dto\Parsing\CommentNode;
use Aeliot\TodoRegistrar\Dto\Parsing\ParsedFile;
use Aeliot\TodoRegistrar\Service\File\Saver;

/**
 * @internal
 */
final class FileHeap
{
    private \Closure $fileUpdateCallback;
    private int $registrationCounter = 0;

    public function __construct(
        private ParsedFile $parsedFile,
        ProcessStatistic $statistic,
        Saver $saver,
    ) {
        $file = $parsedFile->getFile();
        $statistic->setFileRegistrationCount($file->getPathname(), $this->registrationCounter);
        $this->fileUpdateCallback = function () use ($file, $statistic, $saver): void {
            ++$this->registrationCounter;
            $statistic->setFileRegistrationCount($file->getPathname(), $this->registrationCounter);
            $saver->save($file, $this->parsedFile->getAllTokens());
        };
    }

    /**
     * @return CommentNode[]
     */
    public function getCommentNodes(): array
    {
        return $this->parsedFile->getCommentNodes();
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
