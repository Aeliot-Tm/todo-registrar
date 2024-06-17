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
        $tagsPart = implode('|', $tags);
        $keyRegex = implode('|', $this->getTicketKeyRegExpressions());
        $this->pattern = <<<REGEXP
~
^(
    [\s\#*/]*@?(?P<tag>$tagsPart)           # tags
    (?:@(?P<assignee>[a-z0-9._-]+))?        # assignee
    (\s*[:-]?\s+(?P<ticketKey>$keyRegex))?  # keyword/ticket separator & ticket key
    \s*[:-]?\s*                             # optional spaces and colon or hyphen
)
~ix
REGEXP;
    }

    public function getTagMetadata(string $line): ?TagMetadata
    {
        if (!preg_match($this->pattern, $line, $matches, PREG_UNMATCHED_AS_NULL)) {
            return null;
        }

        return new TagMetadata(
            strtoupper($matches['tag']),
            strlen(rtrim($matches[1])),
            $matches['assignee'],
            $matches['ticketKey'],
        );
    }

    /**
     * @return non-empty-array<string>
     */
    private function getTicketKeyRegExpressions(): array
    {
        return [
            // date consisting of YYYY-MM-DD format
            '(?P<date>\d{4}-\d\d?-\d\d?)',
            // github issue URL
            // https://github.com/staabm/phpstan-tod-o-by/issues/91
            '(?P<url>https://github.com/(?P<owner>[\S]{2,})/(?P<repo>[\S]+)/issues/(?P<issueNumber>\d+))',
            // JIRA & YouTack issue
            '(?<issueKey>[A-Z0-9]+-\d+)',
            // "php" or a composer package name, followed by ":" and version
            '(?:(?P<package>(php|[a-z0-9]([_.-]?[a-z0-9]++)*+/[a-z0-9](([_.]|-{1,2})?[a-z0-9]++)*+)):)'
            . '(?P<package_version>[<>=]?[^\s:\-]+)',
            // version
            '(?P<app_version>[<>=]?v?[0-9]+\.[0-9]+(\.[0-9]+)?)',
        ];
    }
}