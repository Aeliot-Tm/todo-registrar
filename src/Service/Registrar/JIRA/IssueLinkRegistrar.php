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

use Aeliot\TodoRegistrarContracts\TodoInterface;
use JiraRestApi\IssueLink\IssueLink;
use JiraRestApi\IssueLink\IssueLinkService;

/**
 * @internal
 */
final readonly class IssueLinkRegistrar
{
    public function __construct(
        private LinkedIssueNormalizer $linkedIssueNormalizer,
        private IssueLinkService $issueLinkService,
    ) {
    }

    public function registerLinks(string $inwardIssueKey, TodoInterface $todo): void
    {
        $linkedIssues = (array) ($todo->getInlineConfig()['linkedIssues'] ?? []);
        if (!$linkedIssues) {
            return;
        }

        $linkedIssues = $this->linkedIssueNormalizer->normalizeLinkedIssues($linkedIssues);

        foreach ($linkedIssues as $issueLinkType => $iterateLinkedIssuesGroup) {
            foreach ($iterateLinkedIssuesGroup as $linkedIssue) {
                $issueLink = $this->createIssueLink($inwardIssueKey, $linkedIssue, $issueLinkType);
                $this->issueLinkService->addIssueLink($issueLink);
            }
        }
    }

    private function createIssueLink(string $inwardIssueKey, string $linkedIssue, string $issueLinkType): IssueLink
    {
        return (new IssueLink())
            ->setInwardIssue($inwardIssueKey)
            ->setOutwardIssue($linkedIssue)
            ->setLinkTypeName($issueLinkType);
    }
}
