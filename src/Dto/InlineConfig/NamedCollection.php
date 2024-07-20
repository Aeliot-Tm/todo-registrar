<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto\InlineConfig;

use Aeliot\TodoRegistrar\Exception\CollectionDuplicatedKeyException;
use Aeliot\TodoRegistrar\Service\InlineConfig\CollectionInterface;

final class NamedCollection implements CollectionInterface
{
    /**
     * @return array<array-key,mixed>
     */
    private array $data = [];

    public function add(string $key, mixed $value): void
    {
        if (array_key_exists($key, $this->data)) {
            throw new CollectionDuplicatedKeyException($key);
        }

        $this->data[$key] = $value;
    }

    public function toArray(): array
    {
        return array_map(
            static fn (mixed $value): mixed => $value instanceof CollectionInterface ? $value->toArray() : $value,
            $this->data,
        );
    }
}