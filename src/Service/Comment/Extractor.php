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

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Comment\CommentParts;
use Aeliot\TodoRegistrar\Dto\Parsing\CommentNode;
use Aeliot\TodoRegistrar\Dto\Token\TokenLine;
use Aeliot\TodoRegistrar\Dto\Token\TokenLinesStack;
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;

/**
 * @internal
 */
final readonly class Extractor
{
    public function __construct(
        private TagDetector $tagDetector,
        private CommentCleanerRegistry $cleanerRegistry,
    ) {
    }

    public function extract(CommentNode $commentNode): CommentParts
    {
        $part = null;
        $parts = new CommentParts();
        $context = $commentNode->getContext();
        $currentLine = $commentNode->getTokens()[0]->getLine();

        foreach ($this->getLines($commentNode) as [$tokenLine, $tokenLinesStack]) {
            /** @var TokenLine $tokenLine */
            $content = $tokenLine->getContent();
            if ($part && !$this->hasEmptyPrefix($content, $part)) {
                $part = null;
            }

            if (null === $part) {
                $part = new CommentPart($currentLine, $this->tagDetector->getTagMetadata($content), $context);
                $parts->addPart($part);
            }

            $part->addLine($tokenLine);
            $part->addTokenLinesStack($tokenLinesStack);

            if (null === $part->getTag()) {
                $part = null;
            }

            ++$currentLine;
        }

        return $parts;
    }

    private function hasEmptyPrefix(string $content, CommentPart $part): bool
    {
        if (null === $part->getTag()) {
            return false;
        }

        $prefix = substr($content, 0, (int) $part->getTagMetadata()?->getPrefixLength());

        return '' === trim($prefix);
    }

    /**
     * @return \Generator<array{0: TokenLine, 1: TokenLinesStack}>
     */
    private function getLines(CommentNode $commentNode): \Generator
    {
        foreach ($commentNode->getTokens() as $token) {
            if (!$token->isComment()) {
                continue;
            }

            $tokenLines = $this->cleanerRegistry->getCleaner($token)->clean($token->getText());
            $tokenLinesStack = new TokenLinesStack($token);

            foreach ($tokenLines as $line) {
                $tokenLinesStack->addLine($line);
                yield [$line, $tokenLinesStack];
            }
        }
    }
}
