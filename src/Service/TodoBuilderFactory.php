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
use Aeliot\TodoRegistrar\Dto\GeneralConfig\IssueKeyInjectionConfig;
use Aeliot\TodoRegistrar\Enum\IssueKeyPosition;
use Aeliot\TodoRegistrar\Service\InlineConfig\ExtrasReader;
use Aeliot\TodoRegistrar\Service\InlineConfig\InlineConfigFactory;
use Aeliot\TodoRegistrarContracts\GeneralConfigInterface;
use Aeliot\TodoRegistrarContracts\IssueKeyInjectionAwareGeneralConfigInterface;

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
        $issueKeyPosition = null;
        $newSeparator = null;
        $replaceSeparator = null;
        if ($config instanceof IssueKeyInjectionAwareGeneralConfigInterface) {
            $injectionConfig = $config->getIssueKeyInjectionConfig();
            $issueKeyPosition = $injectionConfig?->getPosition();
            $newSeparator = $injectionConfig?->getNewSeparator();
            $replaceSeparator = $injectionConfig?->getReplaceSeparator();
        }
        $issueKeyPosition = IssueKeyPosition::from($issueKeyPosition ?? IssueKeyInjectionConfig::DEFAULT_ISSUE_KEY_POSITION);
        $replaceSeparator ??= IssueKeyInjectionConfig::DEFAULT_REPLACE_SEPARATOR;

        return new TodoBuilder($inlineConfigFactory, $inlineConfigReader, $issueKeyPosition, $newSeparator, $output, $replaceSeparator);
    }
}
