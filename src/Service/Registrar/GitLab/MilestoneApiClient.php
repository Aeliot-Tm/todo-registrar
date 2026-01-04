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

use Gitlab\Api\Milestones;

/**
 * Client for working with GitLab project milestones.
 * Used to validate milestone existence before creating issues.
 *
 * @internal
 */
final readonly class MilestoneApiClient
{
    public function __construct(
        private Milestones $milestones,
    ) {
    }

    public function findById(int|string $project, int $id): bool
    {
        return null !== $this->getIdByField($project, 'id', $id);
    }

    /**
     * Find milestone by title and return its ID.
     *
     * @return int|null Milestone ID if found, null otherwise
     */
    public function findByTitle(int|string $project, string $title): ?int
    {
        return $this->getIdByField($project, 'title', $title);
    }

    /**
     * Find milestone by IID and return its ID.
     *
     * @param int $iid Milestone IID (project-specific ID)
     *
     * @return int|null Milestone ID if found, null otherwise
     */
    public function findByIid(int|string $project, int $iid): ?int
    {
        return $this->getIdByField($project, 'iid', $iid);
    }

    private function getIdByField(int|string $project, string $field, int|string $value): ?int
    {
        $milestones = $this->milestones->all($project);
        foreach ($milestones as $milestone) {
            if (isset($milestone['id']) && ($milestone[$field] ?? null) === $value) {
                return (int) $milestone['id'];
            }
        }

        return null;
    }
}
