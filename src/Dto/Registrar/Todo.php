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

namespace Aeliot\TodoRegistrar\Dto\Registrar;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrarContracts\InlineConfigInterface;
use Aeliot\TodoRegistrarContracts\TodoInterface;

class Todo implements TodoInterface
{
    public function __construct(
        protected string $tag,
        protected string $summary,
        protected string $description,
        protected ?string $assignee,
        protected CommentPart $commentPart,
        protected InlineConfigInterface $inlineConfig,
    ) {
    }

    public function getAssignee(): ?string
    {
        return $this->assignee;
    }

    /**
     * @internal
     */
    public function getCommentPart(): CommentPart
    {
        return $this->commentPart;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getInlineConfig(): InlineConfigInterface
    {
        return $this->inlineConfig;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @internal
     */
    public function injectKey(string $key): void
    {
        $this->commentPart->injectKey($key);
    }
}
