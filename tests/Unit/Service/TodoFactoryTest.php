<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Service;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Service\TodoFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TodoFactory::class)]
final class TodoFactoryTest extends TestCase
{
    public function testMappingOfBaseFields(): void
    {
        $tagMetadata = $this->createMock(TagMetadata::class);
        $tagMetadata->method('getAssignee')->willReturn('assignee');

        $commentPart = $this->createMock(CommentPart::class);
        $commentPart->method('getSummary')->willReturn('summary');
        $commentPart->method('getDescription')->willReturn('description');
        $commentPart->method('getTag')->willReturn('a-tag');
        $commentPart->method('getTagMetadata')->willReturn($tagMetadata);

        $todo = (new TodoFactory())->create($commentPart);

        self::assertSame('a-tag', $todo->getTag());
        self::assertSame('summary', $todo->getSummary());
        self::assertSame('description', $todo->getDescription());
        self::assertSame('assignee', $todo->getAssignee());
    }

    public function testMappingOfAssigneeWithoutTagMetadata(): void
    {
        $commentPart = $this->createMock(CommentPart::class);
        $commentPart->method('getSummary')->willReturn('summary');
        $commentPart->method('getDescription')->willReturn('description');
        $commentPart->method('getTag')->willReturn('a-tag');
        $commentPart->method('getTagMetadata')->willReturn(null);

        $todo = (new TodoFactory())->create($commentPart);

        self::assertNull($todo->getAssignee());
    }
}
