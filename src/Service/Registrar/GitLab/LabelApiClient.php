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
        private Projects $projects,
        private int|string $projectIdentifier,
    ) {
    }

    /**
     * Get all labels for the project.
     *
     * @return string[] Array of label names
     */
    public function getAll(): array
    {
        $labels = $this->projects->labels($this->projectIdentifier);

        return array_map(static fn (array $label): string => $label['name'], $labels);
    }

    /**
     * Create a new label in the project.
     *
     * @param string $name Label name
     * @param string|null $color Label color (hex format, e.g., "#FF0000"). If not provided, uses default color.
     * @param string|null $description Label description
     */
    public function create(string $name, ?string $color = null, ?string $description = null): void
    {
        // GitLab API requires color field, so we use a default if not provided
        $params = [
            'name' => $name,
            'color' => $color ?? '#428BCA', // Default blue color if not specified
        ];

        if (null !== $description) {
            $params['description'] = $description;
        }

        $this->projects->addLabel($this->projectIdentifier, $params);
    }
}
