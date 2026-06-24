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

final class CustomFieldIdProvider
{
    /**
     * @var array<string, string>|null
     */
    private ?array $customFieldsMapping = null;

    public function __construct(
        private readonly GeneralIssueConfig $generalIssueConfig,
        private readonly CustomFieldIdFinder $customFieldIdFinder,
    ) {
    }

    public function getId(string $nameOrId): string
    {
        $this->customFieldsMapping ??= $this->generalIssueConfig->getCustomFieldsMapping() ?? [];
        $this->customFieldsMapping[$nameOrId] ??= $this->customFieldIdFinder->getId($nameOrId);
        if (!isset($this->customFieldsMapping[$nameOrId])) {
            throw new InvalidCustomFieldNameException($nameOrId);
        }

        return $this->customFieldsMapping[$nameOrId];
    }
}
