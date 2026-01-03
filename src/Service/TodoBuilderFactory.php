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

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Console\OutputAdapter;
use Aeliot\TodoRegistrar\Service\InlineConfig\ExtrasReader;
use Aeliot\TodoRegistrar\Service\InlineConfig\InlineConfigFactory;
use Aeliot\TodoRegistrarContracts\GeneralConfigInterface;

/**
 * @internal
 */
final readonly class TodoBuilderFactory
{
    public function __construct(
        private ExtrasReader $extrasReader,
        private InlineConfigFactory $inlineConfigFactory,
    ) {
    }

    public function create(GeneralConfigInterface $config, OutputAdapter $output): TodoBuilder
    {
        $inlineConfigReader = $config->getInlineConfigReader() ?? $this->extrasReader;
        $inlineConfigFactory = $config->getInlineConfigFactory() ?? $this->inlineConfigFactory;

        return new TodoBuilder($inlineConfigFactory, $inlineConfigReader, $output);
    }
}
