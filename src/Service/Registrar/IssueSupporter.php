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

namespace Aeliot\TodoRegistrar\Service\Registrar;

use Aeliot\TodoRegistrarContracts\TodoInterface;

final class IssueSupporter
{
    /**
     * @return array<string>
     */
    public function getAssignees(TodoInterface $todo, AbstractGeneralIssueConfig $generalIssueConfig): array
    {
        $assignees = [
            $todo->getAssignee(),
            ...(array) ($todo->getInlineConfig()['assignee'] ?? []),
            ...(array) ($todo->getInlineConfig()['assignees'] ?? []),
        ];
        if (method_exists($generalIssueConfig, 'getAssignee')) {
            $assignees = [...$assignees, ...(array) $generalIssueConfig->getAssignee()];
        }
        if (method_exists($generalIssueConfig, 'getAssignees')) {
            $assignees = [...$assignees, ...$generalIssueConfig->getAssignees()];
        }

        return array_values(array_unique(array_filter($assignees, static fn ($value): bool => '' !== (string) $value)));
    }

    /**
     * @return string[]
     */
    public function getLabels(TodoInterface $todo, AbstractGeneralIssueConfig $generalIssueConfig): array
    {
        $labels = array_filter([
            ...(array) ($todo->getInlineConfig()['labels'] ?? []),
            ...$generalIssueConfig->getLabels(),
        ], static fn ($value): bool => '' !== (string) $value);

        if ($generalIssueConfig->isAddTagToLabels()) {
            $labels[] = strtolower(\sprintf('%s%s', $generalIssueConfig->getTagPrefix(), $todo->getTag()));
        }

        $labels = array_unique($labels);
        if ($allowedLabels = $generalIssueConfig->getAllowedLabels()) {
            $labels = array_intersect($labels, $allowedLabels);
        }

        sort($labels);

        return $labels;
    }

    public function getSummary(TodoInterface $todo, AbstractGeneralIssueConfig $generalIssueConfig): string
    {
        return $generalIssueConfig->getSummaryPrefix() . $todo->getSummary();
    }
}
