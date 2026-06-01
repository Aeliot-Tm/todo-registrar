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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\File;

use Aeliot\TodoRegistrar\Dto\FileHeap;
use Aeliot\TodoRegistrar\Dto\Parsing\CommentNode;
use Aeliot\TodoRegistrar\Dto\Parsing\ParsedFile;
use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Service\File\Parser\YamlFileParser;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrarContracts\Context\ContextNodeInterface;
use Aeliot\TodoRegistrarContracts\Context\YamlContextNodeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(YamlFileParser::class)]
final class YamlFileParserContextTest extends TestCase
{
    private const COMPLEX_CONTEXT_FIXTURE_PATH = __DIR__ . '/../../../fixtures/complex_context.yaml';
    private const TODO_INSIDE_KEY_FIXTURE_DIR = __DIR__ . '/../../../fixtures/yaml';

    /**
     * @return iterable<string, array{0: string, 1: array<int, string>}>
     */
    public static function provideTodoInsideKeyFixtures(): iterable
    {
        $dir = self::TODO_INSIDE_KEY_FIXTURE_DIR;

        yield 'todo_inside_key_with_block_sequence_01.yaml' => [
            'todo_inside_key_with_block_sequence_01.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_block_sequence_01.yaml -> Document: 0 -> Key: [first, second] -> Sequence item: 0",
            ],
        ];

        yield 'todo_inside_key_with_block_sequence_02.yaml' => [
            'todo_inside_key_with_block_sequence_02.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_block_sequence_02.yaml -> Document: 0 -> Key: [first, second] -> Sequence item: 1",
            ],
        ];

        yield 'todo_inside_key_with_flow_mapping_01.yaml' => [
            'todo_inside_key_with_flow_mapping_01.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_flow_mapping_01.yaml -> Document: 0 -> Key: {a: 1} -> Key: a",
            ],
        ];

        yield 'todo_inside_key_with_flow_mapping_02.yaml' => [
            'todo_inside_key_with_flow_mapping_02.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_flow_mapping_02.yaml -> Document: 0 -> Key: {a: 1} -> Key: a",
            ],
        ];

        yield 'todo_inside_key_with_flow_mapping_03.yaml' => [
            'todo_inside_key_with_flow_mapping_03.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_flow_mapping_03.yaml -> Document: 0 -> Key: {a: 1, b: 2} -> Key: b",
            ],
        ];

        yield 'todo_inside_key_with_flow_mapping_04.yaml' => [
            'todo_inside_key_with_flow_mapping_04.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_flow_mapping_04.yaml -> Document: 0 -> Key: {a: 1, b: 2} -> Key: b",
            ],
        ];

        yield 'todo_inside_key_with_flow_sequence_01.yaml' => [
            'todo_inside_key_with_flow_sequence_01.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_flow_sequence_01.yaml -> Document: 0 -> Key: [a] -> Sequence item: 0",
            ],
        ];

        yield 'todo_inside_key_with_flow_sequence_02.yaml' => [
            'todo_inside_key_with_flow_sequence_02.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_flow_sequence_02.yaml -> Document: 0 -> Key: [a, b] -> Sequence item: 1",
            ],
        ];

        yield 'todo_inside_key_with_flow_sequence_03.yaml' => [
            'todo_inside_key_with_flow_sequence_03.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_flow_sequence_03.yaml -> Document: 0 -> Key: [a, {b: val}] -> Sequence item: 1 -> Key: b",
            ],
        ];

        yield 'todo_inside_key_with_flow_sequence_04.yaml' => [
            'todo_inside_key_with_flow_sequence_04.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_flow_sequence_04.yaml -> Document: 0 -> Key: [a, {b: val}] -> Sequence item: 1 -> Key: b",
            ],
        ];

        yield 'todo_inside_key_with_flow_sequence_05.yaml' => [
            'todo_inside_key_with_flow_sequence_05.yaml',
            [
                0 => "File: {$dir}/todo_inside_key_with_flow_sequence_05.yaml -> Document: 0 -> Key: [a, {b: val}] -> Sequence item: 1 -> Key: b",
            ],
        ];

        yield 'todo_inside_value_block_sequence_01.yaml' => [
            'todo_inside_value_block_sequence_01.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_block_sequence_01.yaml -> Document: 0 -> Key: 01 -> Sequence item: 0",
            ],
        ];

        yield 'todo_inside_value_block_sequence_02.yaml' => [
            'todo_inside_value_block_sequence_02.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_block_sequence_02.yaml -> Document: 0 -> Key: 02 -> Sequence item: 1",
            ],
        ];

        yield 'todo_inside_value_flow_mapping_01.yaml' => [
            'todo_inside_value_flow_mapping_01.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_flow_mapping_01.yaml -> Document: 0 -> Key: 01 -> Key: a",
            ],
        ];

        yield 'todo_inside_value_flow_mapping_02.yaml' => [
            'todo_inside_value_flow_mapping_02.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_flow_mapping_02.yaml -> Document: 0 -> Key: 02 -> Key: a",
            ],
        ];

        yield 'todo_inside_value_flow_mapping_03.yaml' => [
            'todo_inside_value_flow_mapping_03.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_flow_mapping_03.yaml -> Document: 0 -> Key: 03 -> Key: b",
            ],
        ];

        yield 'todo_inside_value_flow_mapping_04.yaml' => [
            'todo_inside_value_flow_mapping_04.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_flow_mapping_04.yaml -> Document: 0 -> Key: 04 -> Key: b",
            ],
        ];

        yield 'todo_inside_value_flow_sequence_01.yaml' => [
            'todo_inside_value_flow_sequence_01.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_flow_sequence_01.yaml -> Document: 0 -> Key: 01 -> Sequence item: 0",
            ],
        ];

        yield 'todo_inside_value_flow_sequence_02.yaml' => [
            'todo_inside_value_flow_sequence_02.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_flow_sequence_02.yaml -> Document: 0 -> Key: 02 -> Sequence item: 1",
            ],
        ];

        yield 'todo_inside_value_flow_sequence_03.yaml' => [
            'todo_inside_value_flow_sequence_03.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_flow_sequence_03.yaml -> Document: 0 -> Key: 03 -> Sequence item: 1 -> Key: b",
            ],
        ];

        yield 'todo_inside_value_flow_sequence_04.yaml' => [
            'todo_inside_value_flow_sequence_04.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_flow_sequence_04.yaml -> Document: 0 -> Key: 04 -> Sequence item: 1 -> Key: b",
            ],
        ];

        yield 'todo_inside_value_flow_sequence_05.yaml' => [
            'todo_inside_value_flow_sequence_05.yaml',
            [
                0 => "File: {$dir}/todo_inside_value_flow_sequence_05.yaml -> Document: 0 -> Key: 05 -> Sequence item: 1 -> Key: b",
            ],
        ];

        yield 'todo_with_flow_sequence_key_01.yaml' => [
            'todo_with_flow_sequence_key_01.yaml',
            [
                0 => "File: {$dir}/todo_with_flow_sequence_key_01.yaml -> Document: 0 -> Key: [a, {b: val}]",
            ],
        ];

        yield 'todo_with_several_flow_sequence_key.yaml' => [
            'todo_with_several_flow_sequence_key.yaml',
            [
                0 => "File: {$dir}/todo_with_several_flow_sequence_key.yaml -> Document: 0 -> Key: [a] -> Sequence item: 0",
                1 => "File: {$dir}/todo_with_several_flow_sequence_key.yaml -> Document: 0 -> Key: [b] -> Sequence item: 0",
            ],
        ];

        yield 'todo_with_duplicate_flow_sequence_key.yaml' => [
            'todo_with_duplicate_flow_sequence_key.yaml',
            [
                0 => "File: {$dir}/todo_with_duplicate_flow_sequence_key.yaml -> Document: 0 -> Key: [a] #0 -> Sequence item: 0",
                1 => "File: {$dir}/todo_with_duplicate_flow_sequence_key.yaml -> Document: 0 -> Key: [a] #1 -> Sequence item: 0",
            ],
        ];

        yield 'todo_with_duplicate_key.yaml' => [
            'todo_with_duplicate_key.yaml',
            [
                0 => "File: {$dir}/todo_with_duplicate_key.yaml -> Document: 0 -> Key: a #0",
                1 => "File: {$dir}/todo_with_duplicate_key.yaml -> Document: 0 -> Key: a #1",
            ],
        ];
    }

    public function testParseComplexFileWithContexts(): void
    {
        $parsedFile = $this->parseFixture(self::COMPLEX_CONTEXT_FIXTURE_PATH);
        $commentNodes = $this->getCommentNodes($parsedFile);

        self::assertCount(8, $commentNodes, 'Expected 8 TODO comments in fixture');

        $expectedContexts = $this->getComplexContextExpectedContexts(self::COMPLEX_CONTEXT_FIXTURE_PATH);

        foreach ($commentNodes as $index => $commentNode) {
            $actualPath = $this->buildContextPath($commentNode->getContext()->getContextNodes());

            self::assertArrayHasKey($index, $expectedContexts, "Missing expected context for comment #{$index}");
            self::assertSame(
                $expectedContexts[$index],
                $actualPath,
                "Context mismatch for comment #{$index}: {$commentNode->getTokens()[0]->getText()}",
            );
        }
    }

    public function testLazyContextMapInitialization(): void
    {
        $parsedFile = $this->parseFixture(self::COMPLEX_CONTEXT_FIXTURE_PATH);
        $commentNodes = $this->getCommentNodes($parsedFile);

        self::assertGreaterThan(0, \count($commentNodes), 'Expected at least one comment');

        $firstCommentNode = $commentNodes[0];

        $reflectionClass = new \ReflectionClass($firstCommentNode->getContext());
        $contextMapProperty = $reflectionClass->getProperty('contextMap');
        $contextMapProperty->setAccessible(true);
        $contextMapObject = $contextMapProperty->getValue($firstCommentNode->getContext());

        self::assertInstanceOf(\ArrayAccess::class, $contextMapObject, 'contextMap should implement ArrayAccess');

        $lazyMapReflection = new \ReflectionClass($contextMapObject);
        $internalContextMapProperty = $lazyMapReflection->getProperty('contextMap');
        $internalContextMapProperty->setAccessible(true);

        self::assertNull(
            $internalContextMapProperty->getValue($contextMapObject),
            'contextMap should be null before first access',
        );

        $context = $firstCommentNode->getContext()->getContextNodes();

        self::assertIsArray($context, 'getContextNodes should return array');

        self::assertIsArray(
            $internalContextMapProperty->getValue($contextMapObject),
            'contextMap should be initialized after first access',
        );
    }

    public function testLazyContextMapSharedBetweenComments(): void
    {
        $parsedFile = $this->parseFixture(self::COMPLEX_CONTEXT_FIXTURE_PATH);
        $commentNodes = $this->getCommentNodes($parsedFile);

        self::assertGreaterThanOrEqual(2, \count($commentNodes), 'Expected at least two comments');

        $reflectionClass = new \ReflectionClass($commentNodes[0]->getContext());
        $contextMapProperty = $reflectionClass->getProperty('contextMap');
        $contextMapProperty->setAccessible(true);

        $firstContextMap = $contextMapProperty->getValue($commentNodes[0]->getContext());
        $secondContextMap = $contextMapProperty->getValue($commentNodes[1]->getContext());

        self::assertSame(
            $firstContextMap,
            $secondContextMap,
            'All comments in the same file should share the same LazyContextMap instance',
        );
    }

    #[DataProvider('provideTodoInsideKeyFixtures')]
    public function testParseTodoInsideKeyFixtureContextPaths(string $fixtureName, array $expectedContexts): void
    {
        $fixturePath = self::TODO_INSIDE_KEY_FIXTURE_DIR . '/' . $fixtureName;
        self::assertFileExists($fixturePath);

        $parsedFile = $this->parseFixture($fixturePath);
        $commentNodes = $this->getCommentNodes($parsedFile);

        self::assertCount(
            \count($expectedContexts),
            $commentNodes,
            "Unexpected comment count in fixture {$fixtureName}",
        );

        foreach ($commentNodes as $index => $commentNode) {
            $actualPath = $this->buildContextPath($commentNode->getContext()->getContextNodes());

            self::assertArrayHasKey($index, $expectedContexts, "Missing expected context for comment #{$index} in {$fixtureName}");
            self::assertSame(
                $expectedContexts[$index],
                $actualPath,
                "Context mismatch for comment #{$index} in {$fixtureName}: {$commentNode->getTokens()[0]->getText()}",
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function getComplexContextExpectedContexts(string $filePath): array
    {
        return [
            0 => "File: {$filePath} -> Document: 0",
            1 => "File: {$filePath} -> Document: 0 -> Key: services -> Key: app -> Key: parameters",
            2 => "File: {$filePath} -> Document: 0 -> Key: services -> Key: app -> Key: parameters -> Key: env",
            3 => "File: {$filePath} -> Document: 0 -> Key: services -> Key: items -> Sequence item: 1",
            4 => "File: {$filePath} -> Document: 0 -> Key: services -> Key: flow_items -> Sequence item: 1",
            5 => "File: {$filePath} -> Document: 0 -> Key: services -> Key: flow_map -> Key: nested_key",
            6 => "File: {$filePath} -> Document: 0 -> Key: services -> Key: lookahead_seq -> Sequence item: 0",
            7 => "File: {$filePath} -> Document: 1 -> Key: doc_two",
        ];
    }

    /**
     * @param ContextNodeInterface[] $nodes
     */
    private function buildContextPath(array $nodes): string
    {
        $lines = [];

        foreach ($nodes as $node) {
            $lines[] = match ($node->getKind()) {
                YamlContextNodeInterface::KIND_FILE => "File: {$node->getName()}",
                YamlContextNodeInterface::KIND_DOCUMENT => 'Document: ' . $node->getName(),
                YamlContextNodeInterface::KIND_KEY => 'Key: ' . ($node->getName() ?? '{unknown}'),
                YamlContextNodeInterface::KIND_SEQUENCE_ITEM => 'Sequence item: ' . ($node->getName() ?? '{unknown}'),
                default => ucfirst($node->getKind()) . ($node->getName() ? ': ' . $node->getName() : ''),
            };
        }

        return implode(' -> ', $lines);
    }

    /**
     * @return CommentNode[]
     */
    private function getCommentNodes(ParsedFile $parsedFile): array
    {
        $statistic = new ProcessStatistic();
        $saver = $this->createMock(Saver::class);
        $fileHeap = new FileHeap($parsedFile, false, $statistic, $saver);

        return $fileHeap->getCommentNodes();
    }

    private function getMockSplFileInfo(string $pathname): \SplFileInfo
    {
        $mock = $this->createMock(\SplFileInfo::class);
        $mock->method('getPathname')->willReturn($pathname);

        return $mock;
    }

    private function parseFixture(string $fixturePath): ParsedFile
    {
        return (new YamlFileParser())->parse($this->getMockSplFileInfo($fixturePath));
    }
}
