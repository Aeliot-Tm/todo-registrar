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

namespace Aeliot\TodoRegistrar\Service\Tag;

use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;

/**
 * @internal
 */
final readonly class Detector
{
    private string $pattern;

    /**
     * @param non-empty-array<string> $tags
     * @param non-empty-array<string> $separators
     */
    public function __construct(array $tags = ['todo', 'fixme'], array $separators = [':', '-'])
    {
        $tagsPart = implode('|', $tags);
        $keyRegex = implode('|', $this->getTicketKeyRegExpressions());
        $sepRegex = implode('|', $this->getSeparatorExpressions($separators));
        $this->pattern = implode('', [
            '~^(?P<prefix>',
            "[\s\#*/]*@?(?P<tag>$tagsPart)",
            "(?:@(?P<assignee>[a-z0-9._-]+\b))?",
            "(?:\s*(?P<sepBefore>$sepRegex))?",
            "\s*(?P<ticketKey>$keyRegex)?",
            "\s*(?P<sepAfter>$sepRegex)?\s*",
            ')~ix',
        ]);
    }

    /**
     * @param non-empty-array<string> $separators
     *
     * @return non-empty-array<string>
     */
    public function getSeparatorExpressions(array $separators): array
    {
        return array_map(
            static fn (string $sep): string => preg_quote($sep, '~'),
            $separators
        );
    }

    /**
     * @param array<string,array{0:string|null,1:int}> $matches
     */
    private function getSeparatorOffset(array $matches): ?int
    {
        foreach (['sepBefore', 'sepAfter'] as $key) {
            if (null !== $matches[$key][0]) {
                return $matches[$key][1];
            }
        }

        return null;
    }

    public function getTagMetadata(string $line): ?TagMetadata
    {
        if (!preg_match($this->pattern, $line, $matches, \PREG_UNMATCHED_AS_NULL | \PREG_OFFSET_CAPTURE)) {
            return null;
        }

        return new TagMetadata(
            strtoupper($matches['tag'][0]),
            \strlen(rtrim($matches['prefix'][0])),
            $matches['assignee'][0],
            $this->getSeparatorOffset($matches),
            $matches['ticketKey'][0],
        );
    }

    /**
     * @return non-empty-array<string>
     */
    private function getTicketKeyRegExpressions(): array
    {
        return [
            // date consisting of YYYY-MM-DD format
            '(?:\d{4}-\d\d?-\d\d?)',
            // GitHub issue URL
            '(?:https://github\.com/\S{2,}/\S+/issues/\d++)',
            // GitHub pull request number of exact repo
            '(?:\S{2,}/\S+\#\d++)',
            // GitHub issue number
            '(?:\#\d++)',
            // JIRA & YouTack issue
            '(?:[A-Z0-9]++-\d++)',
            // "php" or a composer package name, followed by ":" and version
            '(?:php|[a-z0-9](?:[_.-]?[a-z0-9]++)*+/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]++)*+):(?:[<>=]?[^\s:\-]+)',
            // version
            '(?:[<>=]?v?[0-9]++(\.[0-9]++){0,2})',
        ];
    }
}
