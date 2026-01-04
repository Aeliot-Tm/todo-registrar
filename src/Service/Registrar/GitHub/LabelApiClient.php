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

use Github\Api\Issue\Labels as LabelsApi;

/**
 * @internal
 */
final class LabelApiClient
{
    /**
     * @var array<string, string[]>
     */
    private array $labelsCache = [];

    public function __construct(
        private readonly LabelsApi $labelsApi,
    ) {
    }

    /**
     * @return string[]
     */
    public function getAll(string $owner, string $repository): array
    {
        $cacheKey = $owner . '/' . $repository;
        if (!isset($this->labelsCache[$cacheKey])) {
            $this->labelsCache[$cacheKey] = array_map(
                static fn (array $x): string => $x['name'],
                $this->labelsApi->all($owner, $repository),
            );
            sort($this->labelsCache[$cacheKey]);
        }

        return $this->labelsCache[$cacheKey];
    }

    public function create(string $owner, string $repository, string $label): void
    {
        $cacheKey = $owner . '/' . $repository;
        if (!isset($this->labelsCache[$cacheKey])) {
            $this->getAll($owner, $repository);
        }

        $this->labelsApi->create($owner, $repository, ['name' => $label]);
        $this->labelsCache[$cacheKey][] = $label;
        sort($this->labelsCache[$cacheKey]);
    }
}
