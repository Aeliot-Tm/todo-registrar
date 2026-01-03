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

use Aeliot\TodoRegistrarContracts\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\TodoInterface;

/**
 * @internal
 */
final readonly class GitlabRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueApiClient $issueApiClient,
        private IssueFactory $issueFactory,
        private LabelApiClient $labelApiClient,
        private MilestoneApiClient $milestoneApiClient,
    ) {
    }

    public function register(TodoInterface $todo): string
    {
        $issue = $this->issueFactory->create($todo);

        // Register missing labels before creating issue
        $labels = $issue->getLabels();
        if (!empty($labels)) {
            $this->registerLabels($labels);
        }

        // Validate milestone if specified
        $milestoneId = $this->extractMilestoneId($issue);
        if (null !== $milestoneId) {
            $this->validateMilestone($milestoneId);
        }

        // Create issue
        $response = $this->issueApiClient->create($issue);

        // Return IID in format "#123"
        return '#' . $response['iid'];
    }

    /**
     * Extract milestone ID from issue data.
     */
    private function extractMilestoneId(Issue $issue): ?int
    {
        $data = $issue->getData();

        return isset($data['milestone_id']) ? (int) $data['milestone_id'] : null;
    }

    /**
     * Register missing labels in the project.
     *
     * @param string[] $labels
     */
    private function registerLabels(array $labels): void
    {
        $existingLabels = $this->labelApiClient->getAll();
        $missingLabels = array_diff($labels, $existingLabels);

        foreach ($missingLabels as $label) {
            $this->labelApiClient->create($label);
        }
    }

    /**
     * Validate that milestone exists.
     */
    private function validateMilestone(int $milestoneId): void
    {
        if (!$this->milestoneApiClient->findById($milestoneId)) {
            throw new \RuntimeException(\sprintf('Milestone with ID %d does not exist', $milestoneId));
        }
    }
}
