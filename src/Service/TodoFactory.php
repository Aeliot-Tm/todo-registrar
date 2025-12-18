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

use Aeliot\TodoRegistrar\Contracts\InlineConfigFactoryInterface;
use Aeliot\TodoRegistrar\Contracts\InlineConfigInterface;
use Aeliot\TodoRegistrar\Contracts\InlineConfigReaderInterface;
use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Registrar\Todo;

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
