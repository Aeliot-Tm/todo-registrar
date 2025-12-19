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

namespace Aeliot\TodoRegistrar\Service\InlineConfig;

use Aeliot\TodoRegistrar\Contracts\InlineConfigFactoryInterface;
use Aeliot\TodoRegistrar\Contracts\InlineConfigInterface;
use Aeliot\TodoRegistrar\Dto\InlineConfig\InlineConfig;

final class InlineConfigFactory implements InlineConfigFactoryInterface
{
    /**
     * TODO: #125 create config specific for configured registrar.
     */
    public function getInlineConfig(array $input): InlineConfigInterface
    {
        return new InlineConfig($input);
    }
}
