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

use Aeliot\TodoRegistrar\Service\Registrar\AbstractGeneralIssueConfig;

final class GeneralIssueConfig extends AbstractGeneralIssueConfig
{
    /**
     * @var string[]
     */
    protected array $assignees;

    /**
     * @return string[]
     */
    public function getAssignees(): array
    {
        return $this->assignees;
    }

    protected function normalizeConfig(array $config): array
    {
        $config = parent::normalizeConfig($config);
        $config += [
            'assignees' => [],
        ];

        return $config;
    }
}
