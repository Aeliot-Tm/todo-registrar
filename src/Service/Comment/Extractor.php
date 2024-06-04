<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Comment;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Comment\CommentParts;
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;

final class Extractor
{
    public function __construct(private TagDetector $tagDetector = new TagDetector())
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

        $prefixLength = $part->getPosition() + strlen($part->getTag());
        $prefix = substr($line, 0, $prefixLength);

        return strlen($prefix) === $prefixLength && \in_array(trim($prefix), ['*', '//', '#'], true);
    }

    public function registerPart(string $line, CommentParts $parts): CommentPart
    {
        $part = new CommentPart(...$this->tagDetector->getTagMetadata($line));
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
        return preg_split("/[\r\n]+/", $comment, -1, PREG_SPLIT_DELIM_CAPTURE);
    }
}