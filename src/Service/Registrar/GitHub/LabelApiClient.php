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
use Aeliot\TodoRegistrar\Service\ColorGenerator;
use Github\Api\Issue\Labels as LabelsApi;
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
final class LabelApiClient
{
    /**
     * @var array<string, string[]>
     */
    private array $labelsCache = [];

    public function __construct(
        private readonly ColorGenerator $colorGenerator,
        private readonly LabelsApi $labelsApi,
    ) {
    }

    /**
     * @throws AuthenticationException
     * @throws LimitExceededException
     * @throws LogicException
     * @throws UnexpectedResponseException
     */
    public function create(string $owner, string $repository, string $label): void
    {
        $cacheKey = $owner . '/' . $repository;
        if (!isset($this->labelsCache[$cacheKey])) {
            $this->getAll($owner, $repository);
        }

        try {
            $this->labelsApi->create($owner, $repository, [
                'name' => $label,
                'color' => $this->colorGenerator->generateColor($label),
            ]);
        } catch (MissingArgumentException $exception) {
            throw new LogicException('Cannot create label case of missing API argument', 0, $exception);
        } catch (ApiLimitExceedException $exception) {
            throw new LimitExceededException('Cannot create label case of GitHub API limit exceeded', 0, $exception);
        } catch (SsoRequiredException|TwoFactorAuthenticationRequiredException $exception) {
            throw new AuthenticationException('Cannot create label case of Invalid authentication to GitHud', 0, $exception);
        } catch (ValidationFailedException $exception) {
            throw new UnexpectedResponseException('Cannot create label case of GitHud request validation failed', 0, $exception);
        } catch (ErrorException $exception) {
            throw new UnexpectedResponseException('Cannot create label case of GitHud invalid request', 0, $exception);
        } catch (RuntimeException $exception) {
            throw new UnexpectedResponseException('Cannot create label case of GitHud request failed', 0, $exception);
        }

        $this->labelsCache[$cacheKey][] = $label;
        sort($this->labelsCache[$cacheKey]);
    }

    /**
     * @return string[]
     *
     * @throws AuthenticationException
     * @throws LimitExceededException
     * @throws UnexpectedResponseException
     */
    public function getAll(string $owner, string $repository): array
    {
        $cacheKey = $owner . '/' . $repository;
        if (!isset($this->labelsCache[$cacheKey])) {
            try {
                $this->labelsCache[$cacheKey] = array_map(
                    static fn (array $x): string => $x['name'],
                    $this->labelsApi->all($owner, $repository),
                );
            } catch (ApiLimitExceedException $exception) {
                throw new LimitExceededException('Cannot get list of labels case of GitHub API limit exceeded', 0, $exception);
            } catch (SsoRequiredException|TwoFactorAuthenticationRequiredException $exception) {
                throw new AuthenticationException('Cannot get list of labels case of Invalid authentication to GitHud', 0, $exception);
            } catch (ValidationFailedException $exception) {
                throw new UnexpectedResponseException('Cannot get list of labels case of GitHud request validation failed', 0, $exception);
            } catch (ErrorException $exception) {
                throw new UnexpectedResponseException('Cannot get list of labels case of GitHud invalid request', 0, $exception);
            } catch (RuntimeException $exception) {
                throw new UnexpectedResponseException('Cannot get list of labels case of GitHud request failed', 0, $exception);
            }

            sort($this->labelsCache[$cacheKey]);
        }

        return $this->labelsCache[$cacheKey];
    }
}
