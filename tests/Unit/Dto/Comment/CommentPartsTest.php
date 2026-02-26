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

namespace Aeliot\TodoRegistrar\Test\Unit\Dto\Comment;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Comment\CommentParts;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentParts::class)]
final class CommentPartsTest extends TestCase
{
    public function testChoiceOfPartByTag(): void
    {
        $commentParts = new CommentParts();
        $commentParts->addPart($this->mockPartWithTag(null));
        $commentParts->addPart($this->mockPartWithTag('A'));
        $commentParts->addPart($this->mockPartWithTag(null));
        $commentParts->addPart($this->mockPartWithTag('B'));
        $commentParts->addPart($this->mockPartWithTag(null));

        $todos = array_values($commentParts->getTodos());
        self::assertCount(2, $todos);
        self::assertEquals('A', $todos[0]->getTag());
        self::assertEquals('B', $todos[1]->getTag());
    }

    private function mockPartWithTag(?string $tag): CommentPart&MockObject
    {
        $part = $this->createMock(CommentPart::class);
        $part->method('getTag')->willReturn($tag);

        return $part;
    }
}
