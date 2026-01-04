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

use Aeliot\TodoRegistrar\Service\ColorGenerator;
use Gitlab\Client;

/**
 * @internal
 */
final readonly class ApiSectionClientFactory
{
    public function __construct(
        private Client $client,
        private ColorGenerator $colorGenerator,
    ) {
    }

    public function createIssueService(): IssueApiClient
    {
        return new IssueApiClient($this->client->issues());
    }

    public function createLabelService(): LabelApiClient
    {
        return new LabelApiClient($this->colorGenerator, $this->client->projects());
    }

    public function createMilestoneService(): MilestoneApiClient
    {
        return new MilestoneApiClient($this->client->milestones());
    }

    public function createUserResolver(): UserResolver
    {
        return new UserResolver($this->client);
    }
}
