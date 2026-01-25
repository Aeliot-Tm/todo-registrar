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

use Aeliot\TodoRegistrar\Service\ContextPath\ContextPathBuilderRegistry;
use Aeliot\TodoRegistrarContracts\ContextAwareTodoInterface;
use Aeliot\TodoRegistrarContracts\TodoInterface;

final readonly class IssueSupporter
{
    public function __construct(
        private ContextPathBuilderRegistry $contextPathBuilderRegistry,
    ) {
    }

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

    public function getDescription(TodoInterface $todo, AbstractGeneralIssueConfig $generalIssueConfig): string
    {
        $description = $todo->getDescription();

        if (
            $todo instanceof ContextAwareTodoInterface
            && ($context = $todo->getContext())
            && ($showContext = ($todo->getInlineConfig()['showContext'] ?? $generalIssueConfig->getShowContext()))
        ) {
            $description .= "\n\n";
            if ($contextTitle = ($todo->getInlineConfig()['contextTitle'] ?? $generalIssueConfig->getContextTitle())) {
                $description .= $contextTitle . "\n";
            }
            $description .= $this->contextPathBuilderRegistry->getBuilder($showContext)->build($context);
        }

        return $description;
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

        return array_values(array_map('trim', $labels));
    }

    public function getSummary(TodoInterface $todo, AbstractGeneralIssueConfig $generalIssueConfig): string
    {
        return $this->getSummaryPrefix($todo, $generalIssueConfig) . $todo->getSummary();
    }

    public function getSummaryPrefix(TodoInterface $todo, AbstractGeneralIssueConfig $generalIssueConfig): string
    {
        return preg_replace_callback('/\\{(?:assignee|tag|tag_caps)}/iu', function (
            array $matches,
        ) use ($todo, $generalIssueConfig): string {
            return match (strtolower($matches[0])) {
                '{assignee}' => ($this->getAssignees($todo, $generalIssueConfig)[0] ?? ''),
                '{tag}' => $todo->getTag(),
                '{tag_caps}' => mb_strtoupper($todo->getTag()),
                // unreachable statement but leave it here
                default => throw new \RuntimeException('Unknown issue summary tag: ' . $matches[0]),
            };
        }, $generalIssueConfig->getSummaryPrefix());
    }
}
