<?php
declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Contracts\GeneralConfigInterface;
use Aeliot\TodoRegistrar\Service\InlineConfig\ExtrasReader;
use Aeliot\TodoRegistrar\Service\InlineConfig\InlineConfigFactory;

final readonly class TodoBuilderFactory
{
    public function __construct(
        private ExtrasReader $extrasReader,
        private InlineConfigFactory $inlineConfigFactory,
    ) {
    }

    public function create(GeneralConfigInterface $config): TodoBuilder
    {
        $inlineConfigReader = $config->getInlineConfigReader() ?? $this->extrasReader;
        $inlineConfigFactory = $config->getInlineConfigFactory() ?? $this->inlineConfigFactory;

        return new TodoBuilder($inlineConfigFactory, $inlineConfigReader);
    }
}
