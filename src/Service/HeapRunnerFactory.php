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
use Aeliot\TodoRegistrar\Service\Comment\Detector as CommentDetector;
use Aeliot\TodoRegistrar\Service\Comment\Extractor as CommentExtractor;
use Aeliot\TodoRegistrar\Service\Config\ConfigProvider;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\File\Tokenizer;

/**
 * @internal
 */
final readonly class HeapRunnerFactory
{
    public function __construct(
        private CommentDetector $commentDetector,
        private CommentExtractor $commentExtractor,
        private ConfigProvider $configProvider,
        private RegistrarProvider $registrarProvider,
        private Saver $saver,
        private TodoBuilder $todoBuilder,
        private Tokenizer $tokenizer,
    ) {
    }

    public function create(?string $configPath, OutputAdapter $output): HeapRunner
    {
        $config = $this->configProvider->getConfig($configPath);
        $registrar = $this->registrarProvider->getRegistrar($config);

        return new HeapRunner(
            $this->commentDetector,
            $this->commentExtractor,
            $config->getFinder(),
            $output,
            $registrar,
            $this->saver,
            $this->todoBuilder,
            $this->tokenizer,
        );
    }
}
