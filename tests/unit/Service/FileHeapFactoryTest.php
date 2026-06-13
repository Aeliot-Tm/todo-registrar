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
use Aeliot\TodoRegistrar\Exception\LogicException;
use Aeliot\TodoRegistrar\Service\Comment\CommentNodesBuilder;
use Aeliot\TodoRegistrar\Service\Comment\SequentialCommentGlueGateRegistry;
use Aeliot\TodoRegistrar\Service\File\FileParserInterface;
use Aeliot\TodoRegistrar\Service\File\FileParserRegistry;
use Aeliot\TodoRegistrar\Service\File\Parser\PhpFileParser;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\FileHeapFactory;
use Aeliot\TodoRegistrar\Test\unit\Service\Support\ProcessingTestSupport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(FileHeapFactory::class)]
#[UsesClass(FileHeap::class)]
#[UsesClass(CommentNodesBuilder::class)]
final class FileHeapFactoryTest extends TestCase
{
    use ProcessingTestSupport;

    public function testReturnsNullWhenParserIsNotConfigured(): void
    {
        $output = new BufferedOutput();
        $context = $this->createHeapContext($output);
        $file = new \SplFileInfo(__DIR__ . '/../../fixtures/single_line.php');
        $fileParserRegistry = $this->createMock(FileParserRegistry::class);
        $fileParserRegistry->method('findParser')->with('php')->willReturn(null);

        $fileHeap = $this->createFactory($fileParserRegistry)->create($file, $context);

        self::assertNull($fileHeap);
        self::assertStringContainsString(
            'There is not configured parser for file:',
            $output->fetch(),
        );
    }

    public function testCreatesFileHeapWhenParserIsAvailable(): void
    {
        $context = $this->createHeapContext();
        $pathname = __DIR__ . '/../../fixtures/single_line.php';
        $file = new \SplFileInfo($pathname);
        $fileParserRegistry = $this->createMock(FileParserRegistry::class);
        $fileParserRegistry->method('findParser')->with('php')->willReturn(new PhpFileParser());

        $fileHeap = $this->createFactory($fileParserRegistry)->create($file, $context);

        self::assertInstanceOf(FileHeap::class, $fileHeap);
        self::assertNotEmpty($fileHeap->getCommentNodes());
        self::assertSame(1, $context->statistic->getCountAnalyzedFiles());
    }

    public function testUsesExtensionAliasFromContext(): void
    {
        $context = $this->createHeapContext();
        $context->extensionAliases = ['module' => 'php'];
        $path = sys_get_temp_dir() . '/todo-registrar-unit-' . uniqid('', true) . '.module';
        file_put_contents($path, "<?php\n\n// TODO: aliased extension\n");
        try {
            $file = new \SplFileInfo($path);
            $fileParserRegistry = $this->createMock(FileParserRegistry::class);
            $fileParserRegistry->expects(self::once())->method('findParser')->with('php')->willReturn(new PhpFileParser());

            $fileHeap = $this->createFactory($fileParserRegistry)->create($file, $context);

            self::assertInstanceOf(FileHeap::class, $fileHeap);
        } finally {
            $this->removeFile($path);
        }
    }

    public function testThrowsWhenSequentialGlueIsEnabledWithoutGate(): void
    {
        $context = $this->createHeapContext();
        $context->glueSequentialComments = true;
        $file = new \SplFileInfo(__DIR__ . '/../../fixtures/single_line.php');
        $fileParserRegistry = $this->createMock(FileParserRegistry::class);
        $fileParserRegistry->method('findParser')->willReturn($this->createMock(FileParserInterface::class));
        $glueGateRegistry = $this->createMock(SequentialCommentGlueGateRegistry::class);
        $glueGateRegistry->method('find')->with('php')->willReturn(null);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Sequential comment glue is enabled but no glue gate is configured');

        (new FileHeapFactory(
            new CommentNodesBuilder(),
            $fileParserRegistry,
            $glueGateRegistry,
            new Saver(),
        ))->create($file, $context);
    }

    private function createFactory(FileParserRegistry $fileParserRegistry): FileHeapFactory
    {
        $glueGateRegistry = $this->createMock(SequentialCommentGlueGateRegistry::class);

        return new FileHeapFactory(
            new CommentNodesBuilder(),
            $fileParserRegistry,
            $glueGateRegistry,
            new Saver(),
        );
    }
}
