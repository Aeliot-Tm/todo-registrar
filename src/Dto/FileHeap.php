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
use Aeliot\TodoRegistrar\Service\File\Saver;

/**
 * @internal
 */
final class FileHeap
{
    private \Closure $fileUpdateCallback;
    private int $registrationCounter = 0;

    /**
     * @var CommentNode[]|null
     */
    private ?array $commentNodes = null;

    public function __construct(
        private readonly ParsedFile $parsedFile,
        private readonly bool $glueSequentialComments,
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
        return $this->commentNodes ??= $this->buildCommentNodes();
    }

    public function getFileUpdateCallback(): \Closure
    {
        return $this->fileUpdateCallback;
    }

    public function getRegistrationCounter(): int
    {
        return $this->registrationCounter;
    }

    /**
     * @return CommentNode[]
     */
    private function buildCommentNodes(): array
    {
        $commentNodes = [];
        $group = new CommentTokensGroup();

        foreach ($this->parsedFile->getAllTokens() as $token) {
            if ($this->glueSequentialComments && $token->isSingleLineComment()) {
                $group->addToken($token);
                continue;
            }

            // If gluing is enabled and we have active group, check whitespace
            if ($this->glueSequentialComments && !$group->isEmpty() && !$token->isComment() && '' === trim($token->getText())) {
                // Empty line (multiple line breaks) breaks the group
                if ($group->hasPendingWhitespace() || $this->hasMultipleLineBreaks($token->getText())) {
                    $commentNodes[] = $this->createCommentNode($group->grabToken());
                    continue;
                }
                // Single line break - store as pending
                $group->addWhitespace($token);
                continue;
            }

            // Break group on non-empty, non-comment token
            if (!$token->isComment()) {
                if (!$group->isEmpty() && ('' !== trim($token->getText()))) {
                    $commentNodes[] = $this->createCommentNode($group->grabToken());
                }
                continue;
            }

            // Multi-line comment - flush group and add comment
            if (!$group->isEmpty()) {
                $commentNodes[] = $this->createCommentNode($group->grabToken());
            }
            $commentNodes[] = $this->createCommentNode($token);
        }

        if (!$group->isEmpty()) {
            $commentNodes[] = $this->createCommentNode($group->grabToken());
        }

        return $commentNodes;
    }

    private function createCommentNode(TokenInterface $token): CommentNode
    {
        return new CommentNode($token, new MappedContext($token->getLine(), $this->parsedFile->getContextMap()));
    }

    private function hasMultipleLineBreaks(string $text): bool
    {
        return substr_count($text, "\n") > 1 || substr_count($text, "\r\n") > 1;
    }
}
