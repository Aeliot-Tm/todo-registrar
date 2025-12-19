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

namespace Aeliot\TodoRegistrar\Test\Unit\Service;

use Aeliot\TodoRegistrar\Console\OutputAdapter;
use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\InlineConfig\IndexedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\NamedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\Token;
use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Service\InlineConfig\ArrayFromJsonLikeLexerBuilder;
use Aeliot\TodoRegistrar\Service\InlineConfig\ExtrasReader;
use Aeliot\TodoRegistrar\Service\InlineConfig\InlineConfigFactory;
use Aeliot\TodoRegistrar\Service\InlineConfig\JsonLikeLexer;
use Aeliot\TodoRegistrar\Service\TodoBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(TodoBuilder::class)]
#[UsesClass(ArrayFromJsonLikeLexerBuilder::class)]
#[UsesClass(ExtrasReader::class)]
#[UsesClass(IndexedCollection::class)]
#[UsesClass(JsonLikeLexer::class)]
#[UsesClass(NamedCollection::class)]
#[UsesClass(Token::class)]
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

        $todoFactory = $this->createTodoFactory();
        $todo = $todoFactory->create($commentPart);

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

        $todoFactory = $this->createTodoFactory();
        $todo = $todoFactory->create($commentPart);

        self::assertNull($todo->getAssignee());
    }

    private function createTodoFactory(): TodoBuilder
    {
        return new TodoBuilder(
            new InlineConfigFactory(),
            new ExtrasReader(new ArrayFromJsonLikeLexerBuilder()),
            new OutputAdapter(new NullOutput())
        );
    }
}
