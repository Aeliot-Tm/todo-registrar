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
use Aeliot\TodoRegistrar\Dto\GeneralConfig\ProcessConfig;
use Aeliot\TodoRegistrar\Dto\HeapContext;
use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrarContracts\GeneralConfig\GeneralConfigInterface;
use Aeliot\TodoRegistrarContracts\GeneralConfig\ProcessConfigAwareInterface;
use Aeliot\TodoRegistrarContracts\GeneralConfig\ProcessConfigInterface;

/**
 * @internal
 */
final readonly class HeapContextFactory
{
    public function create(GeneralConfigInterface $config, OutputAdapter $output, bool $isDryRun = false): HeapContext
    {
        $context = new HeapContext();
        $context->statistic = new ProcessStatistic();
        $context->extensionAliases = $this->getExtensionAliases($config);
        $context->hashToKey = [];
        $context->glueSameTickets = $this->getGlueSameTickets($config);
        $context->glueSequentialComments = $this->getGlueSequentialComments($config);
        $context->isDryRun = $isDryRun;
        $context->output = $output;

        return $context;
    }

    /**
     * @return array<string, string>
     */
    private function getExtensionAliases(GeneralConfigInterface $config): array
    {
        return array_map('strtolower', ($config instanceof ProcessConfigAwareInterface
            ? $config->getProcessConfig()?->getExtensionAliases()
            : null) ?? []);
    }

    private function getGlueSameTickets(GeneralConfigInterface $config): bool
    {
        $processConfig = $config instanceof ProcessConfigAwareInterface
            ? $config->getProcessConfig()
            : null;

        $isGlueSameTicket = null;
        if ($processConfig instanceof ProcessConfigInterface) {
            $isGlueSameTicket = $processConfig->isGlueSameTicket();
        }

        return $isGlueSameTicket ?? ProcessConfig::DEFAULT_GLUE_SAME_TICKETS;
    }

    private function getGlueSequentialComments(GeneralConfigInterface $config): bool
    {
        return ($config instanceof ProcessConfigAwareInterface
            ? $config->getProcessConfig()?->isGlueSequentialComments()
            : null) ?? ProcessConfig::DEFAULT_GLUE_SEQUENTIAL_COMMENTS;
    }
}
