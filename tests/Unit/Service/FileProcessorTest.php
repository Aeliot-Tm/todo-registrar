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

use Aeliot\TodoRegistrar\Console\Output;
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
        $commentRegistrar->method('register')->willReturn(0);
        $saver = $this->createMock(Saver::class);
        $saver->expects($this->never())->method('save');
        $tokenizer = $this->createMock(Tokenizer::class);
        $tokenizer->method('tokenize')->willReturn([]);

        $output = $this->createMock(Output::class);

        $processor = new FileProcessor($commentRegistrar, $saver, $tokenizer);
        $processor->process($this->createMock(\SplFileInfo::class), $output);
    }

    public function testSaveWhenRegistered(): void
    {
        $commentRegistrar = $this->createMock(CommentRegistrar::class);
        $commentRegistrar->method('register')->willReturn(1);
        $saver = $this->createMock(Saver::class);
        $saver->expects($this->once())->method('save');
        $tokenizer = $this->createMock(Tokenizer::class);
        $tokenizer->method('tokenize')->willReturn([]);

        $output = $this->createMock(Output::class);

        $processor = new FileProcessor($commentRegistrar, $saver, $tokenizer);
        $processor->process($this->createMock(\SplFileInfo::class), $output);
    }
}
