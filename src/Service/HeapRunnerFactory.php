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
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Exception\LogicException;
use Aeliot\TodoRegistrar\Exception\NotSupportedConfigException;
use Aeliot\TodoRegistrar\Exception\UnavailableConfigException;
use Aeliot\TodoRegistrar\Service\Config\ConfigProvider;
use Aeliot\TodoRegistrarContracts\Exception\InvalidConfigException as InvalidConfigExceptionInterface;
use Aeliot\TodoRegistrarContracts\GeneralConfig\GeneralConfigInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;

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
     * @throws InvalidConfigExceptionInterface
     * @throws LogicException
     * @throws NotSupportedConfigException
     * @throws UnavailableConfigException
     */
    public function create(?string $configPath, OutputAdapter $output, bool $isDryRun = false): HeapRunner
    {
        $config = $this->configProvider->getConfig($configPath);
        $commentExtractor = $this->commentExtractorFactory->create($config);
        $registrar = $this->getRegistrar($config, $isDryRun);
        $todoBuilder = $this->todoBuilderFactory->create($config, $output);
        $fileProcessor = new FileProcessor($commentExtractor, $registrar, $todoBuilder);

        return new HeapRunner(
            $config,
            $this->fileHeapFactory,
            $fileProcessor,
            $this->heapContextFactory,
            $output,
            $isDryRun,
        );
    }

    /**
     * @throws ConfigValidationException
     * @throws InvalidConfigException
     * @throws InvalidConfigExceptionInterface
     */
    private function getRegistrar(GeneralConfigInterface $config, bool $isDryRun): RegistrarInterface
    {
        if ($isDryRun) {
            $registrarType = RegistrarType::DryRun->value;
            $registrarConfig = [];
        } else {
            $registrarType = $config->getRegistrarType();
            $registrarConfig = $config->getRegistrarConfig();
        }

        return $this->registrarProvider->getRegistrar($registrarType, $registrarConfig);
    }
}
