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

use Aeliot\TodoRegistrarContracts\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\TodoInterface;

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

    public function register(TodoInterface $todo): string
    {
        $issue = $this->issueFactory->create($todo);

        if ($issue->getLabels()) {
            $this->registerLabels($issue);
        }

        $response = $this->issueApiClient->create($issue);

        return '#' . $response['number'];
    }

    private function registerLabels(Issue $issue): void
    {
        $owner = $issue->getOwner();
        $repository = $issue->getRepository();
        $labels = array_diff($issue->getLabels(), $this->labelApiClient->getAll($owner, $repository));
        array_walk($labels, fn (string $label) => $this->labelApiClient->create($owner, $repository, $label));
    }
}
