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

use Aeliot\TodoRegistrarContracts\TodoInterface;

/**
 * @internal
 */
final readonly class IssueFactory
{
    public function __construct(private GeneralIssueConfig $generalIssueConfig)
    {
    }

    public function create(TodoInterface $todo): ExtendedIssueCreateRequest
    {
        $request = new ExtendedIssueCreateRequest();

        $request
            ->queue($this->generalIssueConfig->getQueue())
            ->summary($this->generalIssueConfig->getSummaryPrefix() . $todo->getSummary())
            ->description($todo->getDescription())
            ->type($this->getType($todo));

        $this->setPriority($request, $todo);
        $this->setAssignee($request, $todo);
        $this->setTags($request, $todo);

        return $request;
    }

    private function getType(TodoInterface $todo): string
    {
        $inlineConfig = $todo->getInlineConfig();

        return $inlineConfig['issue_type'] ?? $this->generalIssueConfig->getType();
    }

    private function setPriority(ExtendedIssueCreateRequest $request, TodoInterface $todo): void
    {
        $priority = $todo->getInlineConfig()['priority'] ?? $this->generalIssueConfig->getPriority();
        if (null !== $priority) {
            $request->priority($priority);
        }
    }

    private function setAssignee(ExtendedIssueCreateRequest $request, TodoInterface $todo): void
    {
        $assignee = $todo->getAssignee()
            ?? $todo->getInlineConfig()['assignee']
            ?? $this->generalIssueConfig->getAssignee();

        if (null !== $assignee) {
            $request->assignee($assignee);
        }
    }

    private function setTags(ExtendedIssueCreateRequest $request, TodoInterface $todo): void
    {
        $tags = $this->getTags($todo);
        if ($tags) {
            $request->tags($tags);
        }
    }

    /**
     * @return string[]
     */
    private function getTags(TodoInterface $todo): array
    {
        $tags = [
            ...(array) ($todo->getInlineConfig()['labels'] ?? []),
            ...$this->generalIssueConfig->getLabels(),
        ];

        if ($this->generalIssueConfig->isAddTagToLabels()) {
            $tags[] = strtolower(\sprintf('%s%s', $this->generalIssueConfig->getTagPrefix(), $todo->getTag()));
        }

        $tags = array_unique($tags);
        if ($allowedLabels = $this->generalIssueConfig->getAllowedLabels()) {
            $tags = array_intersect($tags, $allowedLabels);
        }

        return array_values($tags);
    }
}
