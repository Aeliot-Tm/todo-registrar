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
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Exception\LogicException;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\Todo\TodoInterface;

/**
 * @internal
 */
final readonly class GitHubRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueApiClient $issueApiClient,
        private IssueFactory $issueFactory,
        private LabelApiClient $labelApiClient,
    ) {
    }

    /**
     * @throws AuthenticationException
     * @throws InvalidConfigException
     * @throws LimitExceededException
     * @throws LogicException
     * @throws UnexpectedResponseException
     */
    public function register(TodoInterface $todo): string
    {
        $issue = $this->issueFactory->create($todo);

        if ($issue->getLabels()) {
            $this->registerLabels($issue);
        }

        $response = $this->issueApiClient->create($issue);

        return '#' . $response['number'];
    }

    /**
     * @throws AuthenticationException
     * @throws LimitExceededException
     * @throws LogicException
     * @throws UnexpectedResponseException
     */
    private function registerLabels(Issue $issue): void
    {
        $owner = $issue->getOwner();
        $repository = $issue->getRepository();
        $labels = array_diff($issue->getLabels(), $this->labelApiClient->getAll($owner, $repository));
        array_walk($labels, fn (string $label) => $this->labelApiClient->create($owner, $repository, $label));
    }
}
