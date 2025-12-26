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

namespace Aeliot\TodoRegistrar\Service\Registrar\YandexTracker;

use Aeliot\TodoRegistrar\Service\Registrar\AbstractGeneralIssueConfig;
use Symfony\Component\Validator\Constraints as Assert;

final class GeneralIssueConfig extends AbstractGeneralIssueConfig
{
    #[Assert\NotNull(message: 'Option "queue" is required')]
    #[Assert\NotBlank(message: 'Option "queue" must not be empty')]
    #[Assert\Type(type: 'string', message: 'Option "queue" must be a string')]
    protected mixed $queue = null;

    #[Assert\NotNull(message: 'Option "type" is required')]
    #[Assert\Type(type: 'string', message: 'Option "type" must be a string')]
    protected mixed $type = null;

    #[Assert\Type(type: 'string', message: 'Option "priority" must be a string')]
    protected mixed $priority = null;

    #[Assert\Type(type: 'string', message: 'Option "assignee" must be a string')]
    protected mixed $assignee = null;

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function getAssignee(): ?string
    {
        return $this->assignee;
    }

    protected function normalizeConfig(array $config): array
    {
        $config = parent::normalizeConfig($config);
        $config += [
            'queue' => '',
            'type' => 'task',
            'priority' => null,
            'assignee' => null,
        ];

        return $config;
    }
}
