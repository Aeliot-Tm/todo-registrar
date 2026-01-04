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

namespace Aeliot\TodoRegistrar\Service\Registrar\Redmine;

use Aeliot\TodoRegistrarContracts\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\TodoInterface;

/**
 * @internal
 */
final readonly class RedmineRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueApiClient $issueApiClient,
        private IssueFactory $issueFactory,
    ) {
    }

    public function register(TodoInterface $todo): string
    {
        $response = $this->issueApiClient->create($this->issueFactory->create($todo));

        $issueId = $this->extractIssueId($response);

        return '#' . $issueId;
    }

    private function extractIssueId(\SimpleXMLElement $response): int
    {
        // Response structure: <issue><id>123</id>...</issue>
        if (isset($response->issue->id)) {
            return (int) $response->issue->id;
        }

        // Fallback: try direct 'id' field
        if (isset($response->id)) {
            return (int) $response->id;
        }

        throw new \RuntimeException('Unable to extract issue ID from Redmine API response');
    }
}
