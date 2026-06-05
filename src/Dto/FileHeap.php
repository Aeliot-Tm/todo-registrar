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
use Aeliot\TodoRegistrar\Service\Comment\CommentNodesBuilder;
use Aeliot\TodoRegistrar\Service\Comment\SequentialCommentGlueGateInterface;
use Aeliot\TodoRegistrar\Service\File\Saver;

/**
 * @internal
 */
final class FileHeap
{
    /**
     * @var CommentNode[]|null
     */
    private ?array $commentNodes = null;

    private FileStatistic $fileStatistic;

    public function __construct(
        private readonly CommentNodesBuilder $commentNodesBuilder,
        private readonly ParsedFile $parsedFile,
        private readonly bool $glueSequentialComments,
        private readonly ?SequentialCommentGlueGateInterface $glueGate,
        private readonly ProcessStatistic $statistic,
        private readonly Saver $saver,
    ) {
        $this->fileStatistic = new FileStatistic($parsedFile->getFile()->getPathname(), $statistic);
    }

    /**
     * @return CommentNode[]
     */
    public function getCommentNodes(): array
    {
        return $this->commentNodes ??= $this->commentNodesBuilder->build(
            $this->parsedFile,
            $this->glueSequentialComments,
            $this->glueGate,
            $this->statistic,
        );
    }

    public function getRegistrationCount(): int
    {
        return $this->fileStatistic->getRegistrationCount();
    }

    public function saveAfterRegistration(): void
    {
        $this->fileStatistic->tickRegistration();
        $this->saver->save($this->parsedFile->getFile(), $this->parsedFile->getTokenStream());
    }
}
