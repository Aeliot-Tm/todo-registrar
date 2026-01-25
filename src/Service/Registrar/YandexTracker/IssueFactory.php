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

namespace Aeliot\TodoRegistrar\Service\Registrar\YandexTracker;

use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
use Aeliot\TodoRegistrarContracts\TodoInterface;

/**
 * @internal
 */
final readonly class IssueFactory
{
    public function __construct(
        private GeneralIssueConfig $generalIssueConfig,
        private IssueSupporter $issueSupporter,
    ) {
    }

    public function create(TodoInterface $todo): ExtendedIssueCreateRequest
    {
        $request = new ExtendedIssueCreateRequest();

        $request
            ->queue($todo->getInlineConfig()['queue'] ?? $this->generalIssueConfig->getQueue())
            ->summary($this->issueSupporter->getSummary($todo, $this->generalIssueConfig))
            ->description($this->issueSupporter->getDescription($todo, $this->generalIssueConfig))
            ->type($this->getType($todo));

        $this->setPriority($request, $todo);
        $this->setAssignee($request, $todo);
        $this->setTags($request, $todo);

        return $request;
    }

    private function setAssignee(ExtendedIssueCreateRequest $request, TodoInterface $todo): void
    {
        $assignees = $this->issueSupporter->getAssignees($todo, $this->generalIssueConfig);
        if ($assignees) {
            $request->assignee(reset($assignees));
        }
    }

    private function setPriority(ExtendedIssueCreateRequest $request, TodoInterface $todo): void
    {
        $priority = $todo->getInlineConfig()['priority'] ?? $this->generalIssueConfig->getPriority();
        if (null !== $priority) {
            $request->priority($priority);
        }
    }

    private function setTags(ExtendedIssueCreateRequest $request, TodoInterface $todo): void
    {
        $tags = $this->issueSupporter->getLabels($todo, $this->generalIssueConfig);
        if ($tags) {
            $request->tags($tags);
        }
    }

    private function getType(TodoInterface $todo): string
    {
        $inlineConfig = $todo->getInlineConfig();

        return $inlineConfig['issue_type'] ?? $this->generalIssueConfig->getType();
    }
}
