<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar;

interface InlineConfigFactoryInterface
{
    /**
     * @param array<array-key,mixed> $input
     */
    public function getInlineConfig(array $input): InlineConfigInterface;
}
