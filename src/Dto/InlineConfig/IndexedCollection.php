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
