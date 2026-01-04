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

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Exception\InvalidInlineConfigFormatException;
use JiraRestApi\IssueLink\IssueLinkType;

/**
 * @internal
 */
final class LinkedIssueNormalizer
{
    public function __construct(
        private string $defaultIssueLinkType,
        private IssueLinkTypeProvider $issueLinkTypeProvider,
    ) {
    }

    /**
     * @param array<string>|array<string,array<string>> $linkedIssues
     *
     * @return array<string,array<string>>
     */
    public function normalizeLinkedIssues(array $linkedIssues): array
    {
        if (array_is_list($linkedIssues)) {
            $linkedIssues = [$this->defaultIssueLinkType => $linkedIssues];
        }

        $result = [];

        /** @var array<string,array<string>> $linkedIssues */
        foreach ($linkedIssues as $issueLinkTypeAlias => $issueKeys) {
            if (!array_is_list($issueKeys)) {
                throw new InvalidInlineConfigFormatException('List of liked issues must be indexed array of strings');
            }
            $issueLinkType = $this->getIssueLinkType($issueLinkTypeAlias);
            $result[$issueLinkType->name] = $issueKeys;
        }

        return $result;
    }

    private function getIssueLinkType(string $alias): IssueLinkType
    {
        try {
            $issueLinkType = $this->issueLinkTypeProvider->getLinkType($alias);
        } catch (NotSupportedLinkTypeException) {
            throw new InvalidInlineConfigFormatException(\sprintf('Not supported issue link type "%s"', $alias));
        }

        return $issueLinkType;
    }
}
