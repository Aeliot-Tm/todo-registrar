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
final readonly class TagMetadata
{
    public function __construct(
        private ?string $tag,
        private ?int $prefixLength,
        private ?string $assignee,
        private ?int $separatorOffset,
        private ?string $ticketKey,
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

    public function getSeparatorOffset(): ?int
    {
        return $this->separatorOffset;
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
