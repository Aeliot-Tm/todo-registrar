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
use Aeliot\TodoRegistrar\Service\Config\ConfigProvider;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final readonly class HeapRunnerFactory
{
    public function __construct(
        private ConfigProvider $configProvider,
        private FileProcessorFactory $fileProcessorFactory,
    ) {
    }

    public function create(?string $configPath, OutputInterface $output): HeapRunner
    {
        $config = $this->configProvider->getConfig($configPath);
        $fileProcessor = $this->fileProcessorFactory->create($config);

        return new HeapRunner(
            $config->getFinder(),
            $fileProcessor,
            new OutputAdapter($output),
        );
    }
}
