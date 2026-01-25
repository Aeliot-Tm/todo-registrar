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

namespace Aeliot\TodoRegistrar\Service\Registrar\GitHub;

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

    public function create(TodoInterface $todo): Issue
    {
        $issue = new Issue();
        $this->setOwnerAndRepository($issue, $todo);
        $issue->setTitle($this->issueSupporter->getSummary($todo, $this->generalIssueConfig));
        $issue->setBody($this->issueSupporter->getDescription($todo, $this->generalIssueConfig));

        $this->setAssignees($issue, $todo);
        $this->setLabels($issue, $todo);

        return $issue;
    }

    private function setAssignees(Issue $issue, TodoInterface $todo): void
    {
        $assignees = $this->issueSupporter->getAssignees($todo, $this->generalIssueConfig);
        foreach ($assignees as $assignee) {
            $issue->addAssignee($assignee);
        }
    }

    private function setLabels(Issue $issue, TodoInterface $todo): void
    {
        $labels = $this->issueSupporter->getLabels($todo, $this->generalIssueConfig);
        foreach ($labels as $label) {
            $issue->addLabel($label);
        }
    }

    private function setOwnerAndRepository(Issue $issue, TodoInterface $todo): void
    {
        $inlineConfig = $todo->getInlineConfig();
        $owner = $inlineConfig['owner'] ?? $this->generalIssueConfig->getOwner();
        $repository = $inlineConfig['repository'] ?? $this->generalIssueConfig->getRepository();

        if (str_contains($repository, '/')) {
            [$owner, $repository] = explode('/', $repository, 2);
        }

        $issue->setOwner($owner);
        $issue->setRepository($repository);
    }
}
