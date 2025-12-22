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

use Gitlab\Api\Issues;

final readonly class IssueApiClient
{
    public function __construct(
        private Issues $issuesApi,
        private int|string $projectIdentifier,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function create(Issue $issue): array
    {
        return $this->issuesApi->create($this->projectIdentifier, $issue->getData());
    }
}
