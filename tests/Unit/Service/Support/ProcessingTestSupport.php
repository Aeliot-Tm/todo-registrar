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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Support;

use Aeliot\TodoRegistrar\Console\OutputAdapter;
use Aeliot\TodoRegistrar\Dto\FileHeap;
use Aeliot\TodoRegistrar\Dto\HeapContext;
use Aeliot\TodoRegistrar\Dto\Parsing\ParsedFile;
use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Enum\IssueKeyPosition;
use Aeliot\TodoRegistrar\Service\Comment\Cleaner\PhpCommentCleaner;
use Aeliot\TodoRegistrar\Service\Comment\Cleaner\YamlCommentCleaner;
use Aeliot\TodoRegistrar\Service\Comment\CommentCleanerRegistry;
use Aeliot\TodoRegistrar\Service\Comment\CommentNodesBuilder;
use Aeliot\TodoRegistrar\Service\Comment\Extractor as CommentExtractor;
use Aeliot\TodoRegistrar\Service\File\Parser\PhpFileParser;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\InlineConfig\ArrayFromJsonLikeLexerBuilder;
use Aeliot\TodoRegistrar\Service\InlineConfig\ExtrasReader;
use Aeliot\TodoRegistrar\Service\InlineConfig\InlineConfigFactory;
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;
use Aeliot\TodoRegistrar\Service\TodoBuilder;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

trait ProcessingTestSupport
{
    protected function createCommentExtractor(): CommentExtractor
    {
        $cleanerRegistry = new CommentCleanerRegistry([
            new PhpCommentCleaner(),
            new YamlCommentCleaner(),
        ]);

        return new CommentExtractor(new TagDetector(['todo', 'fixme'], [':', '-', '>']), $cleanerRegistry);
    }

    protected function createFileHeap(ParsedFile $parsedFile, HeapContext $context): FileHeap
    {
        return new FileHeap(
            new CommentNodesBuilder(),
            $parsedFile,
            $context->glueSequentialComments,
            null,
            $context->statistic,
            new Saver(),
        );
    }

    protected function createHeapContext(
        OutputInterface $output = new NullOutput(),
        bool $glueSameTickets = false,
    ): HeapContext {
        $context = new HeapContext();
        $context->extensionAliases = [];
        $context->glueSameTickets = $glueSameTickets;
        $context->glueSequentialComments = false;
        $context->hashToKey = [];
        $context->output = new OutputAdapter($output);
        $context->statistic = new ProcessStatistic();

        return $context;
    }

    protected function createTempPhpFile(string $content): string
    {
        $path = sys_get_temp_dir() . '/todo-registrar-unit-' . uniqid('', true) . '.php';
        file_put_contents($path, $content);

        return $path;
    }

    protected function createTodoBuilder(): TodoBuilder
    {
        return new TodoBuilder(
            new InlineConfigFactory(),
            new ExtrasReader(new ArrayFromJsonLikeLexerBuilder()),
            IssueKeyPosition::AFTER_SEPARATOR,
            null,
            new OutputAdapter(new NullOutput()),
            false,
        );
    }

    protected function parsePhpFile(string $pathname): ParsedFile
    {
        return (new PhpFileParser())->parse(new \SplFileInfo($pathname));
    }

    protected function removeFile(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }
    }
}
