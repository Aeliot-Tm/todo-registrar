<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Tag;

use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;

class Detector
{
    private string $pattern;

    /**
     * @param string[] $tags
     */
    public function __construct(array $tags = ['todo', 'fixme'])
    {
        $this->pattern = sprintf('/^(.*(%s)(?:@([a-z0-9._-]+))?\b\s*:?)/i', implode('|', $tags));
    }

    public function getTagMetadata(string $line): ?TagMetadata
    {
        if (!preg_match($this->pattern, $line, $matches)) {
            return null;
        }

        return new TagMetadata(strtoupper($matches[2]), strlen($matches[1]), $matches[3] ?? null);
    }
}