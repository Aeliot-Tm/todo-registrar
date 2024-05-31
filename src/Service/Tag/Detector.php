<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Tag;

final class Detector
{
    public function __construct(private string $pattern = '/\\b(todo|fixme)\\b/i')
    {
    }

    /**
     * @return array{0: string|null, 1: int|null}
     */
    public function getTagMetadata(string $line): array
    {
        $tag = preg_match($this->pattern, $line, $matches) ? $matches[1] : null;

        return null === $tag ? [null, null] : [strtoupper($tag), stripos($line, $tag)];
    }
}