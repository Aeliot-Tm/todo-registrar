<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

use Github\Api\Issue as IssueApi;

final class IssueService
{
    public function __construct(
        private IssueApi $issueAPI,
        private string $owner,
        private string $repository,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function create(Issue $issue): array
    {
        return $this->issueAPI->create($this->owner, $this->repository, $issue->getData());
    }
}
