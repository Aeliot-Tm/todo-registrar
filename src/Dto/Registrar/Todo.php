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

namespace Aeliot\TodoRegistrar\Dto\Registrar;

use Aeliot\TodoRegistrar\InlineConfigInterface;

class Todo
{
    public function __construct(
        private string $tag,
        private string $summary,
        private string $description,
        private ?string $assignee,
        private InlineConfigInterface $inlineConfig,
    ) {
    }

    public function getAssignee(): ?string
    {
        return $this->assignee;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getInlineConfig(): InlineConfigInterface
    {
        return $this->inlineConfig;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}
