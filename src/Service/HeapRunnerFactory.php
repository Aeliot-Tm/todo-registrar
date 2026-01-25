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
use Aeliot\TodoRegistrar\Service\File\FileParser;
use Aeliot\TodoRegistrar\Service\File\Saver;

/**
 * @internal
 */
final readonly class HeapRunnerFactory
{
    public function __construct(
        private CommentExtractorFactory $commentExtractorFactory,
        private ConfigProvider $configProvider,
        private FileParser $fileParser,
        private RegistrarProvider $registrarProvider,
        private Saver $saver,
        private TodoBuilderFactory $todoBuilderFactory,
    ) {
    }

    public function create(?string $configPath, OutputAdapter $output): HeapRunner
    {
        $config = $this->configProvider->getConfig($configPath);
        $commentExtractor = $this->commentExtractorFactory->create($config);
        $registrar = $this->registrarProvider->getRegistrar($config);
        $todoBuilder = $this->todoBuilderFactory->create($config, $output);

        return new HeapRunner(
            $commentExtractor,
            $config->getFinder(),
            $this->fileParser,
            $output,
            $registrar,
            $this->saver,
            $todoBuilder,
        );
    }
}
