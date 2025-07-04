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

use Aeliot\TodoRegistrar\InlineConfigInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class InlineConfig implements InlineConfigInterface
{
    protected CamelCaseToSnakeCaseNameConverter $nameConverter;

    /**
     * @param array<array-key,mixed> $data
     */
    public function __construct(
        private array $data,
    ) {
        $this->nameConverter = new CamelCaseToSnakeCaseNameConverter();
    }

    /**
     * @param int|string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        if (array_key_exists($offset, $this->data)) {
            return true;
        }

        $snakeCase = $this->nameConverter->normalize($offset);
        if (array_key_exists($snakeCase, $this->data)) {
            return true;
        }

        $camelCase = $this->nameConverter->denormalize($offset);

        return array_key_exists($camelCase, $this->data);
    }

    /**
     * @param int|string $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (array_key_exists($offset, $this->data)) {
            return $this->data[$offset];
        }

        $snakeCase = $this->nameConverter->normalize($offset);
        if (array_key_exists($snakeCase, $this->data)) {
            return $this->data[$snakeCase];
        }

        $camelCase = $this->nameConverter->denormalize($offset);

        return $this->data[$camelCase];
    }

    /**
     * @param int|string $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException('Setting value is not allowed.');
    }

    /**
     * @param int|string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException('Unsetting value is not allowed.');
    }
}
