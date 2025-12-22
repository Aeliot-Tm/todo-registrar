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

final class LabelApiClient
{
    /**
     * @var string[]|null
     */
    private ?array $labels = null;

    public function __construct(
        private readonly LabelsApi $labelsApi,
        private readonly string $owner,
        private readonly string $repository,
    ) {
    }

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        if (null === $this->labels) {
            $response = $this->labelsApi->all($this->owner, $this->repository);
            $this->labels = array_map(static fn (array $x): string => $x['name'], $response);
            sort($this->labels);
        }

        return $this->labels;
    }

    public function create(string $label): void
    {
        if (null === $this->labels) {
            $this->getAll();
        }

        $params = ['name' => $label];
        $this->labelsApi->create($this->owner, $this->repository, $params);
        $this->labels[] = $label;
        sort($this->labels);
    }
}
