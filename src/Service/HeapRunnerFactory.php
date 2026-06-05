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
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Exception\LogicException;
use Aeliot\TodoRegistrar\Exception\NotSupportedConfigException;
use Aeliot\TodoRegistrar\Exception\UnavailableConfigException;
use Aeliot\TodoRegistrar\Service\Config\ConfigProvider;

/**
 * @internal
 */
final readonly class HeapRunnerFactory
{
    public function __construct(
        private CommentExtractorFactory $commentExtractorFactory,
        private ConfigProvider $configProvider,
        private FileHeapFactory $fileHeapFactory,
        private HeapContextFactory $heapContextFactory,
        private RegistrarProvider $registrarProvider,
        private TodoBuilderFactory $todoBuilderFactory,
    ) {
    }

    /**
     * @throws ConfigValidationException
     * @throws InvalidConfigException
     * @throws LogicException
     * @throws NotSupportedConfigException
     * @throws UnavailableConfigException
     */
    public function create(?string $configPath, OutputAdapter $output): HeapRunner
    {
        $config = $this->configProvider->getConfig($configPath);
        $commentExtractor = $this->commentExtractorFactory->create($config);
        $registrar = $this->registrarProvider->getRegistrar($config);
        $todoBuilder = $this->todoBuilderFactory->create($config, $output);
        $fileProcessor = new FileProcessor($commentExtractor, $registrar, $todoBuilder);

        return new HeapRunner(
            $config,
            $this->fileHeapFactory,
            $fileProcessor,
            $this->heapContextFactory,
            $output,
        );
    }
}
