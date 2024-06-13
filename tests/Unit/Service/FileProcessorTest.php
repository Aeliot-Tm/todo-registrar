<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Service;

use Aeliot\TodoRegistrar\Service\CommentRegistrar;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\File\Tokenizer;
use Aeliot\TodoRegistrar\Service\FileProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileProcessor::class)]
final class FileProcessorTest extends TestCase
{
    public function testNotSaveWithoutRegistering(): void
    {
        $commentRegistrar = $this->createMock(CommentRegistrar::class);
        $commentRegistrar->method('register')->willReturn(false);
        $saver = $this->createMock(Saver::class);
        $saver->expects($this->never())->method('save');
        $tokenizer = $this->createMock(Tokenizer::class);
        $tokenizer->method('tokenize')->willReturn([]);

        $processor = new FileProcessor($commentRegistrar, $saver, $tokenizer);
        $processor->process($this->createMock(\SplFileInfo::class));
    }

    public function testSaveWhenRegistered(): void
    {
        $commentRegistrar = $this->createMock(CommentRegistrar::class);
        $commentRegistrar->method('register')->willReturn(true);
        $saver = $this->createMock(Saver::class);
        $saver->expects($this->once())->method('save');
        $tokenizer = $this->createMock(Tokenizer::class);
        $tokenizer->method('tokenize')->willReturn([]);

        $processor = new FileProcessor($commentRegistrar, $saver, $tokenizer);
        $processor->process($this->createMock(\SplFileInfo::class));
    }
}