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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
final class GeneralIssueConfig extends AbstractGeneralIssueConfig
{
    /**
     * @var string[]|null
     */
    #[Assert\Sequentially([
        new Assert\NotNull(message: 'Option "assignees" is required'),
        new Assert\Type(type: 'array', message: 'Option "assignees" must be an array'),
        new Assert\All([new Assert\Type(type: 'string', message: 'Each assignee must be a string GitHub username')]),
    ])]
    protected mixed $assignees = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'Option "owner" is required'),
        new Assert\Type(type: 'string', message: 'Option "owner" must be a string'),
    ])]
    protected mixed $owner = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'Option "repository" is required'),
        new Assert\Type(type: 'string', message: 'Option "repository" must be a string'),
        new Assert\Regex(
            pattern: '~^[^/\\s]+$~',
            message: 'Option "repository" cannot contain "/" and blank character'
        ),
    ])]
    protected mixed $repository = null;

    /**
     * @return string[]
     */
    public function getAssignees(): array
    {
        return $this->assignees;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    /**
     * @param array<string,mixed> $config
     *
     * @return array<string,mixed>
     */
    protected function normalizeConfig(array $config): array
    {
        $config = parent::normalizeConfig($config);
        $config += [
            'assignees' => [],
        ];

        return $config;
    }
}
