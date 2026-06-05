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

namespace Aeliot\TodoRegistrar\Service\Comment;

use Aeliot\TodoRegistrar\Dto\Parsing\CommentNode;
use Aeliot\TodoRegistrar\Dto\Parsing\MappedContext;
use Aeliot\TodoRegistrar\Dto\Parsing\ParsedFile;
use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Dto\Token\CommentTokensGroup;
use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;

/**
 * @internal
 */
final readonly class CommentNodesBuilder
{
    /**
     * @return CommentNode[]
     */
    public function build(
        ParsedFile $parsedFile,
        bool $glueSequentialComments,
        ?SequentialCommentGlueGateInterface $glueGate,
        ProcessStatistic $statistic,
    ): array {
        $commentNodes = [];
        $group = new CommentTokensGroup();
        $stream = $parsedFile->getTokenStream();

        while (!$stream->isEnd()) {
            $token = $stream->current();
            if ($token->isComment()) {
                $statistic->tickCommentToken();
            }

            if ($glueSequentialComments && $glueGate?->canGlueCurrent($stream, !$group->isEmpty())) {
                $group->addToken($token);
                $stream->next();
                continue;
            }

            if (!$token->isComment()) {
                if (!$group->isEmpty()) {
                    if ('' !== trim($token->getText())) {
                        $commentNodes[] = $this->createCommentNode($parsedFile, $group->grabTokens());
                    } elseif (null !== $glueGate) {
                        $commentNodes[] = $this->createCommentNode($parsedFile, $group->grabTokens());
                    }
                }
                $stream->next();
                continue;
            }

            if (!$group->isEmpty()) {
                $commentNodes[] = $this->createCommentNode($parsedFile, $group->grabTokens());
            }
            $commentNodes[] = $this->createCommentNode($parsedFile, [$token]);
            $stream->next();
        }

        if (!$group->isEmpty()) {
            $commentNodes[] = $this->createCommentNode($parsedFile, $group->grabTokens());
        }

        return $commentNodes;
    }

    /**
     * @param TokenInterface[] $tokens
     */
    private function createCommentNode(ParsedFile $parsedFile, array $tokens): CommentNode
    {
        return new CommentNode($tokens, new MappedContext($tokens[0]->getLine(), $parsedFile->getContextMap()));
    }
}
