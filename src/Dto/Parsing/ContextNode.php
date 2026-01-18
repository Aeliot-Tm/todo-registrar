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

namespace Aeliot\TodoRegistrar\Dto\Parsing;

use Aeliot\TodoRegistrarContracts\ContextNodeInterface;

/**
 * Represents a single element in the context hierarchy (file, namespace, class, method, etc.).
 *
 * @internal
 */
final readonly class ContextNode implements ContextNodeInterface
{
    public function __construct(
        private string $kind,
        private ?string $name,
    ) {
    }

    public static function file(string $path): self
    {
        return new self(self::KIND_FILE, $path);
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
