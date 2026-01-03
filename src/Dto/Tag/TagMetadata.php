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

namespace Aeliot\TodoRegistrar\Dto\Tag;

/**
 * @internal
 */
class TagMetadata
{
    public function __construct(
        private ?string $tag = null,
        private ?int $prefixLength = null,
        private ?string $assignee = null,
        private ?string $ticketKey = null,
    ) {
    }

    public function getAssignee(): ?string
    {
        return $this->assignee;
    }

    public function getPrefixLength(): ?int
    {
        return $this->prefixLength;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function getTicketKey(): ?string
    {
        return $this->ticketKey;
    }
}
