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

use Aeliot\TodoRegistrar\Dto\FileHeap;
use Aeliot\TodoRegistrar\Exception\CommentRegistrationException;
use Aeliot\TodoRegistrar\Service\Comment\CommentNodesBuilder;
use Aeliot\TodoRegistrar\Service\FileProcessor;
use Aeliot\TodoRegistrar\Test\Stub\IncrementalKeyRegistrar;
use Aeliot\TodoRegistrar\Test\Unit\Service\Support\ProcessingTestSupport;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileProcessor::class)]
#[UsesClass(FileHeap::class)]
#[UsesClass(CommentNodesBuilder::class)]
final class FileProcessorTest extends TestCase
{
    use ProcessingTestSupport;

    public function testRegistersTodosAndSavesFile(): void
    {
        $path = $this->createTempPhpFile("<?php\n\n// TODO: First task\n// TODO: Second task\n");
        try {
            $context = $this->createHeapContext();
            $fileHeap = $this->createFileHeap($this->parsePhpFile($path), $context);
            $registrar = new IncrementalKeyRegistrar('KEY');

            (new FileProcessor($this->createCommentExtractor(), $registrar, $this->createTodoBuilder()))
                ->process($fileHeap, $context);

            $content = (string) file_get_contents($path);
            self::assertStringContainsString('// TODO: KEY-1 First task', $content);
            self::assertStringContainsString('// TODO: KEY-2 Second task', $content);
            self::assertSame(2, $context->statistic->getCountRegisteredTODOs());
            self::assertSame(2, $fileHeap->getRegistrationCount());
        } finally {
            $this->removeFile($path);
        }
    }

    public function testSkipsTodoWithExistingKey(): void
    {
        $path = $this->createTempPhpFile("<?php\n\n// TODO: PROJ-123 Already registered\n");
        try {
            $context = $this->createHeapContext();
            $fileHeap = $this->createFileHeap($this->parsePhpFile($path), $context);
            $registrar = $this->createMock(RegistrarInterface::class);
            $registrar->expects(self::never())->method('register');

            (new FileProcessor($this->createCommentExtractor(), $registrar, $this->createTodoBuilder()))
                ->process($fileHeap, $context);

            self::assertSame(
                "<?php\n\n// TODO: PROJ-123 Already registered\n",
                (string) file_get_contents($path),
            );
            self::assertSame(1, $context->statistic->getCountIgnoredTodos());
            self::assertSame(0, $fileHeap->getRegistrationCount());
        } finally {
            $this->removeFile($path);
        }
    }

    public function testGlueSameTicketsReusesKeyWithoutSecondRegistrarCall(): void
    {
        $path = $this->createTempPhpFile("<?php\n\n// TODO: Same summary\n// TODO: Same summary\n");
        try {
            $context = $this->createHeapContext(glueSameTickets: true);
            $fileHeap = $this->createFileHeap($this->parsePhpFile($path), $context);
            $registrar = new IncrementalKeyRegistrar('KEY');

            (new FileProcessor($this->createCommentExtractor(), $registrar, $this->createTodoBuilder()))
                ->process($fileHeap, $context);

            $content = (string) file_get_contents($path);
            self::assertSame(2, substr_count($content, 'KEY-1'));
            self::assertStringNotContainsString('KEY-2', $content);
            self::assertSame(2, $context->statistic->getCountRegisteredTODOs());
            self::assertSame(1, $context->statistic->getCountGluedTodos());
            self::assertSame(2, $fileHeap->getRegistrationCount());
        } finally {
            $this->removeFile($path);
        }
    }

    public function testWrapsRegistrarFailureInCommentRegistrationException(): void
    {
        $path = $this->createTempPhpFile("<?php\n\n// TODO: Failing task\n");
        try {
            $context = $this->createHeapContext();
            $fileHeap = $this->createFileHeap($this->parsePhpFile($path), $context);
            $registrar = $this->createMock(RegistrarInterface::class);
            $registrar->method('register')->willThrowException(new \RuntimeException('API down'));

            $this->expectException(CommentRegistrationException::class);
            $this->expectExceptionMessage('Cannot register TODO-comment');

            (new FileProcessor($this->createCommentExtractor(), $registrar, $this->createTodoBuilder()))
                ->process($fileHeap, $context);
        } finally {
            $this->removeFile($path);
        }
    }
}
