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

use Aeliot\TodoRegistrar\Exception\Api\LimitExceededException;
use Aeliot\TodoRegistrar\Exception\Api\UnexpectedResponseException;
use Gitlab\Api\Milestones;
use Gitlab\Exception\ApiLimitExceededException;
use Gitlab\Exception\ErrorException;
use Gitlab\Exception\RuntimeException;
use Gitlab\Exception\ValidationFailedException;

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
     * @param int $iid Milestone IID (project-specific ID)
     *
     * @throws LimitExceededException
     * @throws UnexpectedResponseException
     */
    public function findIdByIid(int|string $project, int $iid): ?int
    {
        return $this->getIdByField($project, 'iid', $iid);
    }

    /**
     * @throws LimitExceededException
     * @throws UnexpectedResponseException
     */
    public function findIdByTitle(int|string $project, string $title): ?int
    {
        return $this->getIdByField($project, 'title', $title);
    }

    /**
     * @throws LimitExceededException
     * @throws UnexpectedResponseException
     */
    public function hasById(int|string $project, int $id): bool
    {
        return null !== $this->getIdByField($project, 'id', $id);
    }

    /**
     * @throws LimitExceededException
     * @throws UnexpectedResponseException
     */
    private function getIdByField(int|string $project, string $field, int|string $value): ?int
    {
        try {
            foreach ($this->milestones->all($project) as $milestone) {
                if (isset($milestone['id']) && ($milestone[$field] ?? null) === $value) {
                    return (int) $milestone['id'];
                }
            }
        } catch (ApiLimitExceededException $exception) {
            throw new LimitExceededException('Cannot get list of milestones case of GitLab API limit exceeded', 0, $exception);
        } catch (ValidationFailedException $exception) {
            throw new UnexpectedResponseException('Cannot get list of milestones case of GitLab request validation failed', 0, $exception);
        } catch (ErrorException $exception) {
            throw new UnexpectedResponseException('Cannot get list of milestones case of GitHud invalid request', 0, $exception);
        } catch (RuntimeException $exception) {
            throw new UnexpectedResponseException('Cannot get list of milestones in GitLab', 0, $exception);
        }

        return null;
    }
}
