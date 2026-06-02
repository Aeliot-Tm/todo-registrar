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
use Gitlab\Api\Issues;
use Gitlab\Exception\ApiLimitExceededException;
use Gitlab\Exception\ErrorException;
use Gitlab\Exception\RuntimeException;
use Gitlab\Exception\ValidationFailedException;

/**
 * @internal
 */
final readonly class IssueApiClient
{
    public function __construct(
        private Issues $issuesApi,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function create(Issue $issue): array
    {
        try {
            return $this->issuesApi->create($issue->getProject(), $issue->getData());
        } catch (ApiLimitExceededException $exception) {
            throw new LimitExceededException('Cannot create issue case of GitLab API limit exceeded', 0, $exception);
        } catch (ValidationFailedException $exception) {
            throw new UnexpectedResponseException('Cannot create issue case of GitLab request validation failed', 0, $exception);
        } catch (ErrorException $exception) {
            throw new UnexpectedResponseException('Cannot create issue case of GitHud invalid request', 0, $exception);
        } catch (RuntimeException $exception) {
            throw new UnexpectedResponseException('Cannot create issue in GitLab', 0, $exception);
        }
    }
}
