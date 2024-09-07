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
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;

class Extractor
{
    public function __construct(private TagDetector $tagDetector)
    {
    }

    public function extract(string $comment): CommentParts
    {
        $part = null;
        $parts = new CommentParts();
        foreach ($this->splitLines($comment) as $line) {
            if ($part && !$this->hasEmptyPrefix($line, $part)) {
                $part = null;
            }

            $part ??= $this->registerPart($line, $parts);
            $part->addLine($line);

            if (null === $part->getTag()) {
                $part = null;
            }
        }

        return $parts;
    }

    private function hasEmptyPrefix(string $line, CommentPart $part): bool
    {
        if (null === $part->getTag()) {
            return false;
        }

        $prefix = substr($line, 0, $part->getPrefixLength());

        return \in_array(trim($prefix), ['*', '//', '#'], true);
    }

    private function registerPart(string $line, CommentParts $parts): CommentPart
    {
        $part = new CommentPart($this->tagDetector->getTagMetadata($line));
        $parts->addPart($part);

        return $part;
    }

    /**
     * Method returns lines with EOL.
     *
     * So, it is no matter for comment rebuild process which one was used for each line.
     * And it will not be the case of their changing.
     *
     * @return string[]
     */
    private function splitLines(string $comment): array
    {
        /** @var string[] $lines */
        $lines = preg_split("/([\r\n]+)/", $comment, -1, \PREG_SPLIT_DELIM_CAPTURE);
        $count = \count($lines);
        $currentLineIndex = 0;
        for ($i = 0; $i < $count;) {
            $nextLineIndex = $i + 1;
            if (!\array_key_exists($nextLineIndex, $lines)) {
                break;
            }
            $nextLine = $lines[$nextLineIndex];
            if (preg_match("/^[\r\n]+$/", $nextLine)) {
                $lines[$currentLineIndex] .= $nextLine;
                // skip next line
                unset($lines[$nextLineIndex]);
                ++$i;
            } else {
                $currentLineIndex = $i = $nextLineIndex;
            }
        }

        return array_values($lines);
    }
}
