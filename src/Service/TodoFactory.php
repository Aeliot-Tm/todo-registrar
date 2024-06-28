<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\InlineConfigReaderInterface;

class TodoFactory
{
    public function __construct(private InlineConfigReaderInterface $inlineConfigReader)
    {
    }

    public function create(CommentPart $commentPart): Todo
    {
        $description = $commentPart->getDescription();

        return new Todo(
            $commentPart->getTag(),
            $commentPart->getSummary(),
            $commentPart->getDescription(),
            $commentPart->getTagMetadata()?->getAssignee(),
            $this->inlineConfigReader->getInlineConfig($description),
        );
    }
}
