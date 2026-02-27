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

namespace Aeliot\TodoRegistrar\Service\Report;

use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Enum\ReportFormat;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final readonly class ReportBuilder
{
    public function format(ReportFormat $format, ProcessStatistic $statistic): string
    {
        if ($format->isNone()) {
            throw new \InvalidArgumentException('Report format NONE cannot be used for formatting');
        }

        $data = $this->buildData($statistic);

        return match ($format) {
            ReportFormat::JSON => json_encode($data, \JSON_THROW_ON_ERROR),
            ReportFormat::YAML => Yaml::dump($data, 4, 2),
            default => throw new \DomainException(\sprintf('Report format "%s" is not supported', $format->value)),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildData(ProcessStatistic $statistic): array
    {
        $files = array_filter($statistic->getFiles());

        return [
            'summary' => [
                'files' => [
                    'analyzed' => $statistic->getCountAnalyzedFiles(),
                    'updated' => $statistic->getCountUpdatedFiles(),
                ],
                'comments' => [
                    'detected' => $statistic->getCountCommentTokens(),
                ],
                'todos' => [
                    'ignored' => $statistic->getCountIgnoredTodos(),
                    'glued' => $statistic->getCountGluedTodos(),
                    'registered' => $statistic->getCountRegisteredTODOs(),
                    'total' => $statistic->getTodosTotal(),
                ],
            ],
            'files' => array_map(
                static fn (string $path, int $count): array => [
                    'path' => $path,
                    'summary' => ['todos' => ['registered' => $count]],
                ],
                array_keys($files),
                $files
            ),
        ];
    }
}
