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

use Github\Api\Issue as IssueApi;

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
     */
    public function create(Issue $issue): array
    {
        return $this->issueAPI->create($issue->getOwner(), $issue->getRepository(), $issue->getData());
    }
}
