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
use Aeliot\TodoRegistrar\Service\ColorGenerator;
use Gitlab\Api\Projects;
use Gitlab\Exception\ApiLimitExceededException;
use Gitlab\Exception\ErrorException;
use Gitlab\Exception\RuntimeException;
use Gitlab\Exception\ValidationFailedException;

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
        try {
            $this->projects->addLabel($project, [
                'name' => $name,
                'color' => $this->colorGenerator->generateColor($name),
            ]);
        } catch (ApiLimitExceededException $exception) {
            throw new LimitExceededException('Cannot create label case of GitLab API limit exceeded', 0, $exception);
        } catch (ValidationFailedException $exception) {
            throw new UnexpectedResponseException('Cannot create label case of GitLab request validation failed', 0, $exception);
        } catch (ErrorException $exception) {
            throw new UnexpectedResponseException('Cannot create label case of GitLab invalid request', 0, $exception);
        } catch (RuntimeException $exception) {
            throw new UnexpectedResponseException('Cannot create label in GitLab', 0, $exception);
        }
    }

    /**
     * Get all labels for the project.
     *
     * @return string[] Array of label names
     */
    public function getAll(int|string $project): array
    {
        try {
            $labels = $this->projects->labels($project);
        } catch (ApiLimitExceededException $exception) {
            throw new LimitExceededException('Cannot get list of labels case of GitLab API limit exceeded', 0, $exception);
        } catch (ValidationFailedException $exception) {
            throw new UnexpectedResponseException('Cannot get list of labels case of GitLab request validation failed', 0, $exception);
        } catch (ErrorException $exception) {
            throw new UnexpectedResponseException('Cannot get list of labels case of GitHud invalid request', 0, $exception);
        } catch (RuntimeException $exception) {
            throw new UnexpectedResponseException('Cannot get list of labels in GitLab', 0, $exception);
        }

        return array_map(static fn (array $label): string => $label['name'], $labels);
    }
}
