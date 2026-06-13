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
use Aeliot\TodoRegistrar\Dto\FileHeap;
use Aeliot\TodoRegistrar\Dto\HeapContext;
use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Service\FileHeapFactory;
use Aeliot\TodoRegistrar\Service\FileProcessor;
use Aeliot\TodoRegistrar\Service\HeapContextFactory;
use Aeliot\TodoRegistrar\Service\HeapRunner;
use Aeliot\TodoRegistrar\Test\unit\Service\Support\ProcessingTestSupport;
use Aeliot\TodoRegistrarContracts\FinderInterface;
use Aeliot\TodoRegistrarContracts\GeneralConfig\GeneralConfigInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(HeapRunner::class)]
final class HeapRunnerTest extends TestCase
{
    use ProcessingTestSupport;

    public function testRunProcessesEachDiscoveredFile(): void
    {
        $fileA = new \SplFileInfo('/tmp/a.php');
        $fileB = new \SplFileInfo('/tmp/b.php');
        $context = $this->createHeapContext();
        $fileHeap = $this->createMock(FileHeap::class);
        $fileHeap->method('getRegistrationCount')->willReturn(1);
        $fileHeap->method('getCommentNodes')->willReturn([]);

        $config = $this->createMock(GeneralConfigInterface::class);
        $config->method('getFinder')->willReturn($this->createFinder([$fileA, $fileB]));

        $heapContextFactory = $this->createMock(HeapContextFactory::class);
        $heapContextFactory->method('create')->willReturn($context);

        $fileHeapFactory = $this->createMock(FileHeapFactory::class);
        $fileHeapFactory->expects(self::exactly(2))->method('create')->willReturn($fileHeap);

        $fileProcessor = $this->createMock(FileProcessor::class);
        $fileProcessor->expects(self::exactly(2))->method('process');

        $statistic = (new HeapRunner(
            $config,
            $fileHeapFactory,
            $fileProcessor,
            $heapContextFactory,
            new OutputAdapter(new BufferedOutput()),
        ))->run();

        self::assertSame($context->statistic, $statistic);
    }

    public function testRunSkipsFileWhenFactoryReturnsNull(): void
    {
        $file = new \SplFileInfo('/tmp/a.php');
        $context = $this->createHeapContext();

        $config = $this->createMock(GeneralConfigInterface::class);
        $config->method('getFinder')->willReturn($this->createFinder([$file]));

        $heapContextFactory = $this->createMock(HeapContextFactory::class);
        $heapContextFactory->method('create')->willReturn($context);

        $fileHeapFactory = $this->createMock(FileHeapFactory::class);
        $fileHeapFactory->method('create')->willReturn(null);

        $fileProcessor = $this->createMock(FileProcessor::class);
        $fileProcessor->expects(self::never())->method('process');

        (new HeapRunner(
            $config,
            $fileHeapFactory,
            $fileProcessor,
            $heapContextFactory,
            new OutputAdapter(new BufferedOutput()),
        ))->run();
    }

    public function testRunWritesErrorAndRethrowsOnFailure(): void
    {
        $file = new \SplFileInfo('/tmp/a.php');
        $context = $this->createHeapContext();
        $fileHeap = $this->createMock(FileHeap::class);
        $output = new BufferedOutput();

        $config = $this->createMock(GeneralConfigInterface::class);
        $config->method('getFinder')->willReturn($this->createFinder([$file]));

        $heapContextFactory = $this->createMock(HeapContextFactory::class);
        $heapContextFactory->method('create')->willReturn($context);

        $fileHeapFactory = $this->createMock(FileHeapFactory::class);
        $fileHeapFactory->method('create')->willReturn($fileHeap);

        $fileProcessor = $this->createMock(FileProcessor::class);
        $fileProcessor->method('process')->willThrowException(new \RuntimeException('Parse failed'));

        try {
            (new HeapRunner(
                $config,
                $fileHeapFactory,
                $fileProcessor,
                $heapContextFactory,
                new OutputAdapter($output),
            ))->run();
            self::fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Parse failed', $exception->getMessage());
        }

        $outputContent = $output->fetch();
        self::assertStringContainsString('[ERROR] Parse failed', $outputContent);
        self::assertStringContainsString('Cannot process file: /tmp/a.php', $outputContent);
    }

    public function testRunReturnsSharedProcessStatistic(): void
    {
        $context = new HeapContext();
        $context->statistic = new ProcessStatistic();
        $context->extensionAliases = [];
        $context->glueSameTickets = false;
        $context->glueSequentialComments = false;
        $context->hashToKey = [];
        $context->output = new OutputAdapter(new BufferedOutput());

        $config = $this->createMock(GeneralConfigInterface::class);
        $config->method('getFinder')->willReturn($this->createFinder([]));

        $heapContextFactory = $this->createMock(HeapContextFactory::class);
        $heapContextFactory->method('create')->willReturn($context);

        $statistic = (new HeapRunner(
            $config,
            $this->createMock(FileHeapFactory::class),
            $this->createMock(FileProcessor::class),
            $heapContextFactory,
            $context->output,
        ))->run();

        self::assertSame($context->statistic, $statistic);
    }

    /**
     * @param \SplFileInfo[] $files
     */
    private function createFinder(array $files): FinderInterface
    {
        $finder = $this->createMock(FinderInterface::class);
        $finder->method('getIterator')->willReturn(new \ArrayIterator($files));

        return $finder;
    }
}
