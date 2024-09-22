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

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

use Github\Api\Issue\Labels as LabelsApi;

/**
 * TODO: cache list of labels
 *       It reduce API-calls and speed up processing of issues with labels.
 */
final class LabelService
{
    public function __construct(
        private LabelsApi $labelsApi,
        private string $owner,
        private string $repository,
    ) {
    }

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        $response = $this->labelsApi->all($this->owner, $this->repository);

        return array_map(static fn (array $x): string => $x['name'], $response);
    }

    public function create(string $label): void
    {
        $params = ['name' => $label];
        $this->labelsApi->create($this->owner, $this->repository, $params);
    }
}
