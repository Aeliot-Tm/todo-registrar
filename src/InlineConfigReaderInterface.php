<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar;

interface InlineConfigReaderInterface
{
    /**
     * @return array<array-key,mixed>
     */
    public function getInlineConfig(string $input): array;
}
