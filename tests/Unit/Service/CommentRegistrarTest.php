<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Service;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\Comment\CommentParts;
use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Service\Comment\Detector as CommentDetector;
use Aeliot\TodoRegistrar\Service\Comment\Extractor as CommentExtractor;
use Aeliot\TodoRegistrar\Service\CommentRegistrar;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentRegistrar::class)]
final class CommentRegistrarTest extends TestCase
{
    public function testDontRegisterTwice(): void
    {
        $tokens = $this->getTokens();
        $commentDetector = $this->mockCommentDetector($tokens);
        $commentParts = $this->createCommentParts();
        $commentExtractor = $this->mockCommentExtractor($commentParts);

        $todo = $commentParts->getTodos()[0];

        $registrar = $this->mockRegistrar($todo, true);
        $registrar
            ->expects($this->never())
            ->method('register')
            ->with($todo);

        $commentRegistrar = new CommentRegistrar($commentDetector, $commentExtractor, $registrar);
        $commentRegistrar->register($tokens);
    }

    public function testRegisterNewTodos(): void
    {
        $tokens = $this->getTokens();
        $commentDetector = $this->mockCommentDetector($tokens);
        $commentParts = $this->createCommentParts();
        $commentExtractor = $this->mockCommentExtractor($commentParts);

        $todo = $commentParts->getTodos()[0];

        $registrar = $this->mockRegistrar($todo, false);
        $registrar
            ->expects($this->once())
            ->method('register')
            ->with($todo);

        $commentRegistrar = new CommentRegistrar($commentDetector, $commentExtractor, $registrar);
        $commentRegistrar->register($tokens);
    }

    public function createCommentParts(): CommentParts
    {
        $commentPart = new CommentPart(new TagMetadata('TODO', 7));
        $commentPart->addLine('// TODO single line comment');
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
        $values = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        return array_map(static fn(array $value): \PhpToken => new \PhpToken(
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

    public function mockRegistrar(CommentPart $todo, bool $isRegistered): RegistrarInterface&MockObject
    {
        $registrar = $this->createMock(RegistrarInterface::class);
        $registrar
            ->expects($this->once())
            ->method('isRegistered')
            ->with($todo)
            ->willReturn($isRegistered);

        return $registrar;
    }
}