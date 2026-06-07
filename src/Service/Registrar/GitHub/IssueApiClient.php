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

use Aeliot\TodoRegistrar\Exception\Api\AuthenticationException;
use Aeliot\TodoRegistrar\Exception\Api\LimitExceededException;
use Aeliot\TodoRegistrar\Exception\Api\UnexpectedResponseException;
use Aeliot\TodoRegistrar\Exception\LogicException;
use Github\Api\Issue as IssueApi;
use Github\Exception\ApiLimitExceedException;
use Github\Exception\ErrorException;
use Github\Exception\MissingArgumentException;
use Github\Exception\RuntimeException;
use Github\Exception\SsoRequiredException;
use Github\Exception\TwoFactorAuthenticationRequiredException;
use Github\Exception\ValidationFailedException;

/**
 * @internal
 */
final readonly class IssueApiClient
{
    public function __construct(
        private IssueApi $issueAPI,
    ) {
    }

    /**
     * @return array<string,mixed>
     *
     * @throws AuthenticationException
     * @throws LimitExceededException
     * @throws LogicException
     * @throws UnexpectedResponseException
     */
    public function create(Issue $issue): array
    {
        try {
            return $this->issueAPI->create($issue->getOwner(), $issue->getRepository(), $issue->getData());
        } catch (MissingArgumentException $exception) {
            throw new LogicException('Cannot create issue case of missing API argument', 0, $exception);
        } catch (ApiLimitExceedException $exception) {
            throw new LimitExceededException('Cannot create issue case of GitHub API limit exceeded', 0, $exception);
        } catch (SsoRequiredException|TwoFactorAuthenticationRequiredException $exception) {
            throw new AuthenticationException('Cannot create issue case of Invalid authentication to GitHud', 0, $exception);
        } catch (ValidationFailedException $exception) {
            throw new UnexpectedResponseException('Cannot create issue case of GitHud request validation failed', 0, $exception);
        } catch (ErrorException $exception) {
            throw new UnexpectedResponseException('Cannot create issue case of GitHud invalid request', 0, $exception);
        } catch (RuntimeException $exception) {
            throw new UnexpectedResponseException('Cannot create issue case of GitHud request failed', 0, $exception);
        }
    }
}
