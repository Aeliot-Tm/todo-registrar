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

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Dto\GeneralConfig\IssueKeyInjectionConfig;
use Aeliot\TodoRegistrar\Service\Comment\CommentCleanerRegistry;
use Aeliot\TodoRegistrar\Service\Comment\Extractor as CommentExtractor;
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;
use Aeliot\TodoRegistrarContracts\GeneralConfig\GeneralConfigInterface;
use Aeliot\TodoRegistrarContracts\GeneralConfig\IssueKeyInjectionConfigAwareInterface;

/**
 * @internal
 */
final readonly class CommentExtractorFactory
{
    public function __construct(private CommentCleanerRegistry $commentCleanerRegistry)
    {
    }

    public function create(GeneralConfigInterface $config): CommentExtractor
    {
        $separators = [];
        if ($config instanceof IssueKeyInjectionConfigAwareInterface) {
            $separators = $config->getIssueKeyInjectionConfig()?->getSummarySeparators();
        }
        if (!$separators) {
            $separators = IssueKeyInjectionConfig::DEFAULT_SEPARATORS;
        }

        $tags = $config->getTags() ?: Config::DEFAULT_TAGS;

        return new CommentExtractor(new TagDetector($tags, $separators), $this->commentCleanerRegistry);
    }
}
