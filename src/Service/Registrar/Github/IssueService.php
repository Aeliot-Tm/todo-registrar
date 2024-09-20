<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

use Github\Api\Issue;

final class IssueService
{
    public function __construct(
        private Issue $issueAPI,
        private string $owner,
        private string $repository,
    ) {
    }

    public function create(array $params): array
    {
        return $this->issueAPI->create($this->owner, $this->repository, $params);
    }
}
