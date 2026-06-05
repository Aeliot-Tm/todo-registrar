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
use Aeliot\TodoRegistrar\Dto\Parsing\MappedContext;
use Aeliot\TodoRegistrar\Dto\Parsing\ParsedFile;
use Aeliot\TodoRegistrar\Dto\Token\CommentTokensGroup;
use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
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
        return $this->commentNodes ??= $this->buildCommentNodes();
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

    /**
     * @return CommentNode[]
     */
    private function buildCommentNodes(): array
    {
        $commentNodes = [];
        $group = new CommentTokensGroup();
        $stream = $this->parsedFile->getTokenStream();

        while (!$stream->isEnd()) {
            $token = $stream->current();
            if ($token->isComment()) {
                $this->statistic->tickCommentToken();
            }

            if ($this->glueSequentialComments && $this->glueGate?->canGlueCurrent($stream, !$group->isEmpty())) {
                $group->addToken($token);
                $stream->next();
                continue;
            }

            if (!$token->isComment()) {
                if (!$group->isEmpty()) {
                    if ('' !== trim($token->getText())) {
                        $commentNodes[] = $this->createCommentNode($group->grabTokens());
                    } elseif (null !== $this->glueGate) {
                        $commentNodes[] = $this->createCommentNode($group->grabTokens());
                    }
                }
                $stream->next();
                continue;
            }

            if (!$group->isEmpty()) {
                $commentNodes[] = $this->createCommentNode($group->grabTokens());
            }
            $commentNodes[] = $this->createCommentNode([$token]);
            $stream->next();
        }

        if (!$group->isEmpty()) {
            $commentNodes[] = $this->createCommentNode($group->grabTokens());
        }

        return $commentNodes;
    }

    /**
     * @param TokenInterface[] $tokens
     */
    private function createCommentNode(array $tokens): CommentNode
    {
        return new CommentNode($tokens, new MappedContext($tokens[0]->getLine(), $this->parsedFile->getContextMap()));
    }
}
