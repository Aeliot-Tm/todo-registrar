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

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

use Aeliot\TodoRegistrar\Contracts\RegistrarInterface;
use Aeliot\TodoRegistrar\Contracts\TodoInterface;

final class GithubRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueFactory $issueFactory,
        private ApiClientFactory $serviceFactory,
    ) {
    }

    public function register(TodoInterface $todo): string
    {
        $issue = $this->issueFactory->create($todo);

        if ($issue->getLabels()) {
            $this->registerLabels($issue->getLabels());
        }

        $response = $this->serviceFactory->createIssueService()->create($issue);

        return '#' . $response['number'];
    }

    /**
     * @param string[] $labels
     */
    private function registerLabels(array $labels): void
    {
        $labelsService = $this->serviceFactory->createLabelService();
        $labels = array_diff($labels, $labelsService->getAll());
        array_walk($labels, static fn (string $label) => $labelsService->create($label));
    }
}
