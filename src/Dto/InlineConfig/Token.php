<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto\InlineConfig;

final class Token
{
    public function __construct(
        private string $value,
        private int $type,
        private int $position,
    ) {
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
