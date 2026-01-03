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

use Gitlab\Client;

/**
 * @internal
 */
final readonly class ApiSectionClientFactory
{
    public function __construct(
        private Client $client,
        private int|string $projectId,
    ) {
    }

    public function createIssueService(): IssueApiClient
    {
        return new IssueApiClient($this->client->issues(), $this->projectId);
    }

    public function createLabelService(): LabelApiClient
    {
        return new LabelApiClient($this->client->projects(), $this->projectId);
    }

    public function createMilestoneService(): MilestoneApiClient
    {
        return new MilestoneApiClient($this->client->milestones(), $this->projectId);
    }

    public function createUserResolver(): UserResolver
    {
        return new UserResolver($this->client);
    }
}
