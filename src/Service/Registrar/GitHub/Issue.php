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

/**
 * @internal
 */
final class Issue
{
    /**
     * @var array<string,mixed>
     */
    private array $data;
    private string $owner;
    private string $repository;

    /**
     * @return array<string,mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function addAssignee(string $assignee): void
    {
        $this->data['assignees'] ??= [];
        $this->data['assignees'][] = $assignee;
    }

    public function setBody(string $body): void
    {
        $this->data['body'] = $body;
    }

    /**
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->data['labels'] ?? [];
    }

    public function addLabel(string $label): void
    {
        $this->data['labels'] ??= [];
        $this->data['labels'][] = $label;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function setRepository(string $repository): void
    {
        $this->repository = $repository;
    }

    public function setTitle(string $title): void
    {
        $this->data['title'] = $title;
    }
}
