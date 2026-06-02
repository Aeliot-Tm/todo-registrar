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

use Aeliot\TodoRegistrar\Exception\LogicException;
use Github\Api\Issue as IssueApi;
use Github\Exception\MissingArgumentException;

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
     *
     * @throws LogicException
     */
    public function create(Issue $issue): array
    {
        try {
            return $this->issueAPI->create($issue->getOwner(), $issue->getRepository(), $issue->getData());
        } catch (MissingArgumentException $exception) {
            throw new LogicException('Cannot create issue case of missing API argument', 0, $exception);
        }
    }
}
