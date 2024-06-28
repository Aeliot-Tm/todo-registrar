<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\InlineConfig;

interface CollectionInterface
{
    /**
     * @return array<array-key,mixed>
     */
    public function toArray(): array;
}
