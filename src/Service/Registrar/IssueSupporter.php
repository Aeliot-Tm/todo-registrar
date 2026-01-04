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
     * @return string[]
     */
    public function getLabels(TodoInterface $todo, AbstractGeneralIssueConfig $generalIssueConfig): array
    {
        $labels = [
            ...(array) ($todo->getInlineConfig()['labels'] ?? []),
            ...$generalIssueConfig->getLabels(),
        ];

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
}
