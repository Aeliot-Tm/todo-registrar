<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\InlineConfigFactoryInterface;
use Aeliot\TodoRegistrar\InlineConfigInterface;
use Aeliot\TodoRegistrar\InlineConfigReaderInterface;

class TodoFactory
{
    public function __construct(
        private InlineConfigFactoryInterface $inlineConfigFactory,
        private InlineConfigReaderInterface $inlineConfigReader,
    ) {
    }

    public function create(CommentPart $commentPart): Todo
    {
        $description = $commentPart->getDescription();

        return new Todo(
            $commentPart->getTag(),
            $commentPart->getSummary(),
            $description,
            $commentPart->getTagMetadata()?->getAssignee(),
            $this->getInlineConfig($description),
        );
    }

    private function getInlineConfig(string $description): InlineConfigInterface
    {
        try {
            $config = $this->inlineConfigReader->getInlineConfig($description);
        } catch (\Throwable $exception) {
            fwrite(\STDERR, "[ERROR] {$exception->getMessage()}. Cannot parse inline config for: $description \n");
            $config = [];
        }

        return $this->inlineConfigFactory->getInlineConfig($config);
    }
}
