<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\InlineConfig;

use Aeliot\TodoRegistrar\Dto\InlineConfig\InlineConfig;
use Aeliot\TodoRegistrar\InlineConfigFactoryInterface;
use Aeliot\TodoRegistrar\InlineConfigInterface;

final class InlineConfigFactory implements InlineConfigFactoryInterface
{
    public function getInlineConfig(array $input): InlineConfigInterface
    {
        return new InlineConfig($input);
    }
}
