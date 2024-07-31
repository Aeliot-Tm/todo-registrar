<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto\InlineConfig;

use Aeliot\TodoRegistrar\Service\InlineConfig\CollectionInterface;

final class IndexedCollection implements CollectionInterface
{
    /**
     * @var array<int,mixed>
     */
    private array $data = [];

    public function add(mixed $value): void
    {
        $this->data[] = $value;
    }

    public function toArray(): array
    {
        return array_map(
            static fn (mixed $value): mixed => $value instanceof CollectionInterface ? $value->toArray() : $value,
            $this->data,
        );
    }
}
