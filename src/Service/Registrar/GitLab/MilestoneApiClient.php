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
use Gitlab\Client;

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

    /**
     * Get all milestones for the project.
     *
     * @return array<int,array<string,mixed>> Array of milestones indexed by ID
     */
    public function getAll(int|string $project): array
    {
        $milestones = $this->milestones->all($project);
        $result = [];
        foreach ($milestones as $milestone) {
            if (isset($milestone['id'])) {
                $result[(int) $milestone['id']] = $milestone;
            }
        }

        return $result;
    }

    /**
     * Get all milestones indexed by IID.
     *
     * @return array<int,array<string,mixed>> Array of milestones indexed by IID
     */
    private function getAllByIid(int|string $project): array
    {
        $milestones = $this->milestones->all($project);
        $result = [];
        foreach ($milestones as $milestone) {
            if (isset($milestone['iid'])) {
                $result[(int) $milestone['iid']] = $milestone;
            }
        }

        return $result;
    }

    /**
     * Find milestone by ID.
     *
     * @return bool True if milestone exists
     */
    public function findById(int|string $project, int $id): bool
    {
        $milestones = $this->getAll($project);

        return isset($milestones[$id]);
    }

    /**
     * Find milestone by title and return its ID.
     *
     * @return int|null Milestone ID if found, null otherwise
     */
    public function findByTitle(int|string $project, string $title): ?int
    {
        $milestones = $this->getAll($project);
        foreach ($milestones as $id => $milestone) {
            if (isset($milestone['title']) && $milestone['title'] === $title) {
                return $id;
            }
        }

        return null;
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
        $milestonesByIid = $this->getAllByIid($project);
        if (isset($milestonesByIid[$iid]) && isset($milestonesByIid[$iid]['id'])) {
            return (int) $milestonesByIid[$iid]['id'];
        }

        return null;
    }
}
