<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Comment;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Comment\CommentParts;

final class Extractor
{
    public function __construct(private string $pattern = '/\\b(todo|fixme)\\b/i')
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

    /**
     * @return array{0: string|null, 1: int|null}
     */
    private function getCommentPosition(string $line): array
    {
        $tag = preg_match($this->pattern, $line, $matches) ? $matches[1] : null;

        return null === $tag ? [null, null] : [strtoupper($tag), stripos($line, $tag)];
    }

    private function hasEmptyPrefix(string $line, CommentPart $part): bool
    {
        if (null === $part->getTag()) {
            return false;
        }

        $prefixLength = $part->getPosition() + strlen($part->getTag());
        $prefix = substr($line, 0, $prefixLength);

        return strlen($prefix) === $prefixLength && \in_array(trim($prefix), ['*', '//', '#'], true);
    }

    public function registerPart(string $line, CommentParts $parts): CommentPart
    {
        $part = new CommentPart(...$this->getCommentPosition($line));
        $parts->addPart($part);

        return $part;
    }

    /**
     * @return string[]
     */
    private function splitLines(string $comment): array
    {
        return preg_split("/\\r?\\n/", $comment);
    }
}