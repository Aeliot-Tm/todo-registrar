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

namespace Aeliot\TodoRegistrar\Test\Unit\AST\YAML;

use Aeliot\TodoRegistrar\AST\YAML\ContextMapBuilder;
use Aeliot\TodoRegistrar\AST\YAML\ContextMapVisitor;
use Aeliot\TodoRegistrar\Dto\Parsing\YamlContextNodeInterface;
use Aeliot\TodoRegistrarContracts\ContextNodeInterface;
use Aeliot\YamlToken\Parser\ParserBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContextMapVisitor::class)]
final class ContextMapVisitorComplexKeyTest extends TestCase
{
    public function testComplexMappingKeyDistinguishesKeyAndValueBranches(): void
    {
        $contextMap = $this->buildContextMapFromFixture('complex_key_context.yaml');

        self::assertSame(
            '{a: 1}',
            $this->findContextNodeName($contextMap, 1, YamlContextNodeInterface::KIND_KEY),
        );
        self::assertSame(
            'a',
            $this->findInnermostContextNodeName($contextMap, 1, YamlContextNodeInterface::KIND_KEY),
        );
        self::assertSame(
            'b',
            $this->findInnermostContextNodeName($contextMap, 2, YamlContextNodeInterface::KIND_KEY),
        );
    }

    public function testComplexSequenceKeyItemsAreInKeyBranch(): void
    {
        $contextMap = $this->buildContextMapFromFixture('complex_sequence_key_context.yaml');

        self::assertSame(
            '[first, second]',
            $this->findContextNodeName($contextMap, 2, YamlContextNodeInterface::KIND_KEY),
        );
        self::assertSame(
            '0',
            $this->findContextNodeName($contextMap, 2, YamlContextNodeInterface::KIND_SEQUENCE_ITEM),
        );
        self::assertSame(
            '1',
            $this->findContextNodeName($contextMap, 3, YamlContextNodeInterface::KIND_SEQUENCE_ITEM),
        );
    }

    public function testDuplicateComplexKeyNamesGetSiblingIndex(): void
    {
        $contextMap = $this->buildContextMapFromFixture('todo_with_duplicate_flow_sequence_key.yaml');

        self::assertSame(
            '[a] #0',
            $this->findContextNodeName($contextMap, 1, YamlContextNodeInterface::KIND_KEY),
        );
        self::assertSame(
            '[a] #1',
            $this->findContextNodeName($contextMap, 2, YamlContextNodeInterface::KIND_KEY),
        );
    }

    #[DataProvider('provideNestedMappingWithoutComplexKey')]
    public function testNestedMappingWithoutComplexKey(string $yaml, int $line, string $expectedKeyName): void
    {
        $contextMap = $this->buildContextMap($yaml);

        self::assertSame(
            $expectedKeyName,
            $this->findInnermostContextNodeName($contextMap, $line, YamlContextNodeInterface::KIND_KEY),
        );
    }

    /**
     * @return iterable<string, array{0: string, 1: int, 2: string}>
     */
    public static function provideNestedMappingWithoutComplexKey(): iterable
    {
        yield 'block mapping' => ["parent:\n  child: value\n", 2, 'child'];

        yield 'flow mapping at document root' => ["{ parent: { child: value } }\n", 1, 'child'];
    }

    /**
     * @return array<int, list<ContextNodeInterface>>
     */
    private function buildContextMap(string $yaml): array
    {
        $parser = (new ParserBuilder())->createParser();
        $streamNode = $parser->parse($yaml);

        return (new ContextMapBuilder($streamNode, 'test.yaml'))->buildContextMap();
    }

    /**
     * @return array<int, list<ContextNodeInterface>>
     */
    private function buildContextMapFromFixture(string $fixtureName): array
    {
        $fixturePath = __DIR__ . '/../../../fixtures/yaml/' . $fixtureName;
        self::assertFileExists($fixturePath);

        $yaml = file_get_contents($fixturePath);
        self::assertIsString($yaml);

        return $this->buildContextMap($yaml);
    }

    /**
     * @param array<int, list<ContextNodeInterface>> $contextMap
     */
    private function findContextNodeName(array $contextMap, int $line, string $kind): ?string
    {
        foreach ($contextMap[$line] ?? [] as $node) {
            if ($node->getKind() === $kind) {
                return $node->getName();
            }
        }

        return null;
    }

    /**
     * @param array<int, list<ContextNodeInterface>> $contextMap
     */
    private function findInnermostContextNodeName(array $contextMap, int $line, string $kind): ?string
    {
        $name = null;

        foreach ($contextMap[$line] ?? [] as $node) {
            if ($node->getKind() === $kind) {
                $name = $node->getName();
            }
        }

        return $name;
    }
}
