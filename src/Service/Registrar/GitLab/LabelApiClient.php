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

namespace Aeliot\TodoRegistrar\Service\Registrar\GitLab;

use Aeliot\TodoRegistrar\Service\ColorGenerator;
use Gitlab\Api\Projects;

/**
 * Client for working with GitLab project labels.
 * Labels are created at project level, not at issue level.
 *
 * @internal
 */
final readonly class LabelApiClient
{
    public function __construct(
        private ColorGenerator $colorGenerator,
        private Projects $projects,
    ) {
    }

    /**
     * Create a new label in the project.
     *
     * @param string $name Label name
     */
    public function create(int|string $project, string $name): void
    {
        $this->projects->addLabel($project, [
            'name' => $name,
            'color' => $this->colorGenerator->generateColor($name),
        ]);
    }

    /**
     * Get all labels for the project.
     *
     * @return string[] Array of label names
     */
    public function getAll(int|string $project): array
    {
        $labels = $this->projects->labels($project);

        return array_map(static fn (array $label): string => $label['name'], $labels);
    }
}
