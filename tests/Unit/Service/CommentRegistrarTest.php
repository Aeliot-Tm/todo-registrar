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

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Comment\CommentParts;
use Aeliot\TodoRegistrar\Dto\InlineConfig\IndexedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\NamedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\Token;
use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Service\Comment\Detector as CommentDetector;
use Aeliot\TodoRegistrar\Service\Comment\Extractor as CommentExtractor;
use Aeliot\TodoRegistrar\Service\CommentRegistrar;
use Aeliot\TodoRegistrar\Service\InlineConfig\ArrayFromJsonLikeLexerBuilder;
use Aeliot\TodoRegistrar\Service\InlineConfig\ExtrasReader;
use Aeliot\TodoRegistrar\Service\InlineConfig\InlineConfigFactory;
use Aeliot\TodoRegistrar\Service\InlineConfig\JsonLikeLexer;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;
use Aeliot\TodoRegistrar\Service\TodoFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(CommentRegistrar::class)]
#[UsesClass(ArrayFromJsonLikeLexerBuilder::class)]
#[UsesClass(CommentPart::class)]
#[UsesClass(CommentParts::class)]
#[UsesClass(ExtrasReader::class)]
#[UsesClass(IndexedCollection::class)]
#[UsesClass(JsonLikeLexer::class)]
#[UsesClass(NamedCollection::class)]
#[UsesClass(TagMetadata::class)]
#[UsesClass(Todo::class)]
#[UsesClass(TodoFactory::class)]
#[UsesClass(Token::class)]
final class CommentRegistrarTest extends TestCase
{
    public function testDontRegisterTwice(): void
    {
        $tokens = $this->getTokens();
        $commentDetector = $this->mockCommentDetector($tokens);
        $commentParts = $this->createCommentParts($tokens[2]->text);
        $commentExtractor = $this->mockCommentExtractor($commentParts);

        $todoFactory = $this->createTodoFactory();
        $todo = $todoFactory->create($commentParts->getTodos()[0]);

        $registrar = $this->mockRegistrar($todo, true);
        $registrar
            ->expects($this->never())
            ->method('register')
            ->with($todo);

        $output = $this->createMock(OutputInterface::class);

        $commentRegistrar = new CommentRegistrar($commentDetector, $commentExtractor, $registrar, $todoFactory);
        $commentRegistrar->register($tokens, $output);

        self::assertSame('// TODO single line comment', $tokens[2]->text);
    }

    public function testDontRegisterTwiceWithIssueKey(): void
    {
        $tokens = $this->getTokens();
        $tokens[2]->text = '// TODO X-001 single line comment';

        $commentDetector = $this->mockCommentDetector($tokens);
        $commentParts = $this->createCommentParts($tokens[2]->text, 'X-001');
        $commentExtractor = $this->mockCommentExtractor($commentParts);

        $todoFactory = $this->createTodoFactory();

        $registrar = $this->createMock(RegistrarInterface::class);
        $registrar
            ->expects($this->never())
            ->method('isRegistered');
        $registrar
            ->expects($this->never())
            ->method('register');

        $output = $this->createMock(OutputInterface::class);

        $commentRegistrar = new CommentRegistrar($commentDetector, $commentExtractor, $registrar, $todoFactory);
        $commentRegistrar->register($tokens, $output);
    }

    public function testRegisterNewTodos(): void
    {
        $tokens = $this->getTokens();
        $commentDetector = $this->mockCommentDetector($tokens);
        $commentParts = $this->createCommentParts($tokens[2]->text);
        $commentExtractor = $this->mockCommentExtractor($commentParts);

        $token = $commentParts->getTodos()[0];
        $todoFactory = $this->createTodoFactory();
        $todo = $todoFactory->create($token);

        $registrar = $this->mockRegistrar($todo, false);
        $registrar
            ->expects($this->once())
            ->method('register')
            ->with($todo)
            ->willReturn('X-001');

        $output = $this->createMock(OutputInterface::class);

        $commentRegistrar = new CommentRegistrar($commentDetector, $commentExtractor, $registrar, $todoFactory);
        $commentRegistrar->register($tokens, $output);

        self::assertSame('// TODO X-001 single line comment', $tokens[2]->text);
    }

    private function createCommentParts(string $line, ?string $ticketKey = null): CommentParts
    {
        $commentPart = new CommentPart(new TagMetadata('TODO', 7, null, $ticketKey));
        $commentPart->addLine($line);
        $commentParts = new CommentParts();
        $commentParts->addPart($commentPart);

        return $commentParts;
    }

    /**
     * @return \PhpToken[]
     */
    private function getTokens(): array
    {
        $json = file_get_contents(__DIR__ . '/../../fixtures/tokens_of_single_line_php.json');
        $values = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

        return array_map(static fn (array $value): \PhpToken => new \PhpToken(
            $value['id'],
            $value['text'],
            $value['line'],
            $value['pos'],
        ), $values);
    }

    /**
     * @param \PhpToken[] $tokens
     */
    private function mockCommentDetector(array $tokens): CommentDetector&MockObject
    {
        $commentDetector = $this->createMock(CommentDetector::class);
        $commentDetector
            ->expects($this->once())
            ->method('filter')
            ->willReturn([$tokens[2]]);

        return $commentDetector;
    }

    private function mockCommentExtractor(CommentParts $commentParts): CommentExtractor&MockObject
    {
        $todoCommentLine = $commentParts->getTodos()[0]->getFirstLine();
        $commentExtractor = $this->createMock(CommentExtractor::class);
        $commentExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($todoCommentLine)
            ->willReturn($commentParts);

        return $commentExtractor;
    }

    private function mockRegistrar(Todo $todo, bool $isRegistered): RegistrarInterface&MockObject
    {
        $registrar = $this->createMock(RegistrarInterface::class);
        $registrar
            ->expects($this->once())
            ->method('isRegistered')
            ->with($todo)
            ->willReturn($isRegistered);

        return $registrar;
    }

    private function createTodoFactory(): TodoFactory
    {
        return new TodoFactory(new InlineConfigFactory(), new ExtrasReader(new ArrayFromJsonLikeLexerBuilder()));
    }
}
