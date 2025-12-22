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

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Contracts\RegistrarInterface;
use Aeliot\TodoRegistrar\Contracts\TodoInterface;
use JiraRestApi\Issue\IssueService;

final readonly class JiraRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueFieldFactory $issueFieldFactory,
        private IssueLinkRegistrar $issueLinkRegistrar,
        private IssueService $issueService,
    ) {
    }

    public function register(TodoInterface $todo): string
    {
        $issueField = $this->issueFieldFactory->create($todo);

        $issueKey = $this->issueService->create($issueField)->key;
        $this->issueLinkRegistrar->registerLinks($issueKey, $todo);

        return $issueKey;
    }
}
