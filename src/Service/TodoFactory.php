<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Registrar\Todo;

class TodoFactory
{
    public function create(CommentPart $commentPart): Todo
    {
        return new Todo(
            $commentPart->getTag(),
            $commentPart->getFirstLine(),
            $commentPart->getContent(),
            $commentPart->getTagMetadata()?->getAssignee(),
        );
    }
}