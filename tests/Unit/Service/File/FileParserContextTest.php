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

use Aeliot\TodoRegistrar\Service\File\FileParser;
use Aeliot\TodoRegistrarContracts\ContextNodeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileParser::class)]
final class FileParserContextTest extends TestCase
{
    private const FIXTURE_PATH = __DIR__ . '/../../../fixtures/complex_context.php';

    public function testParseComplexFileWithContexts(): void
    {
        $file = $this->getMockSplFileInfo(self::FIXTURE_PATH);
        $parser = new FileParser();

        $parsedFile = $parser->parse($file);
        $commentNodes = $parsedFile->getCommentNodes();

        self::assertCount(59, $commentNodes, 'Expected 59 TODO comments in fixture');

        $expectedContexts = $this->getExpectedContexts();

        foreach ($commentNodes as $index => $commentNode) {
            $context = $commentNode->context->getContextNodes();
            $actualPath = $this->buildContextPath($context);

            self::assertArrayHasKey($index, $expectedContexts, "Missing expected context for comment #{$index}");
            self::assertSame(
                $expectedContexts[$index],
                $actualPath,
                "Context mismatch for comment #{$index}: {$commentNode->token->text}"
            );
        }
    }

    public function testLazyContextMapInitialization(): void
    {
        $file = $this->getMockSplFileInfo(self::FIXTURE_PATH);
        $parser = new FileParser();

        $parsedFile = $parser->parse($file);
        $commentNodes = $parsedFile->getCommentNodes();

        self::assertGreaterThan(0, \count($commentNodes), 'Expected at least one comment');

        $firstCommentNode = $commentNodes[0];

        $reflectionClass = new \ReflectionClass($firstCommentNode->context);
        $contextMapProperty = $reflectionClass->getProperty('contextMap');
        $contextMapProperty->setAccessible(true);
        $contextMapObject = $contextMapProperty->getValue($firstCommentNode->context);

        self::assertInstanceOf(\ArrayAccess::class, $contextMapObject, 'contextMap should implement ArrayAccess');

        $lazyMapReflection = new \ReflectionClass($contextMapObject);
        $internalContextMapProperty = $lazyMapReflection->getProperty('contextMap');
        $internalContextMapProperty->setAccessible(true);

        self::assertNull(
            $internalContextMapProperty->getValue($contextMapObject),
            'contextMap should be null before first access'
        );

        $context = $firstCommentNode->context->getContextNodes();

        self::assertIsArray($context, 'getContextNodes should return array');

        self::assertIsArray(
            $internalContextMapProperty->getValue($contextMapObject),
            'contextMap should be initialized after first access'
        );
    }

    public function testLazyContextMapSharedBetweenComments(): void
    {
        $file = $this->getMockSplFileInfo(self::FIXTURE_PATH);
        $parser = new FileParser();

        $parsedFile = $parser->parse($file);
        $commentNodes = $parsedFile->getCommentNodes();

        self::assertGreaterThanOrEqual(2, \count($commentNodes), 'Expected at least two comments');

        $reflectionClass = new \ReflectionClass($commentNodes[0]->context);
        $contextMapProperty = $reflectionClass->getProperty('contextMap');
        $contextMapProperty->setAccessible(true);

        $firstContextMap = $contextMapProperty->getValue($commentNodes[0]->context);
        $secondContextMap = $contextMapProperty->getValue($commentNodes[1]->context);

        self::assertSame(
            $firstContextMap,
            $secondContextMap,
            'All comments in the same file should share the same LazyContextMap instance'
        );
    }

    public function testLazyContextMapIsReadOnly(): void
    {
        $file = $this->getMockSplFileInfo(self::FIXTURE_PATH);
        $parser = new FileParser();

        $parsedFile = $parser->parse($file);
        $commentNodes = $parsedFile->getCommentNodes();

        self::assertGreaterThan(0, \count($commentNodes), 'Expected at least one comment');

        $reflectionClass = new \ReflectionClass($commentNodes[0]->context);
        $contextMapProperty = $reflectionClass->getProperty('contextMap');
        $contextMapProperty->setAccessible(true);
        $contextMapObject = $contextMapProperty->getValue($commentNodes[0]->context);

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('LazyContextMap is read-only');

        $contextMapObject[999] = [];
    }

    /**
     * @return array<int, string>
     */
    private function getExpectedContexts(): array
    {
        $filePath = self::FIXTURE_PATH;

        return [
            0 => "File: {$filePath} -> Namespace: App\Services",
            1 => "File: {$filePath} -> Namespace: App\Services -> Interface: ServiceInterface",
            2 => "File: {$filePath} -> Namespace: App\Services -> Interface: RepositoryInterface",
            3 => "File: {$filePath} -> Namespace: App\Models",
            4 => "File: {$filePath} -> Namespace: App\Models -> Class: User -> Property: name",
            5 => "File: {$filePath} -> Namespace: App\Models -> Class: User",
            6 => "File: {$filePath} -> Namespace: App\Models -> Class: User",
            7 => "File: {$filePath} -> Namespace: App\Models -> Class: User -> Method: getName()",
            8 => "File: {$filePath} -> Namespace: App\Models -> Class: User",
            9 => "File: {$filePath} -> Namespace: App\Models -> Class: User -> Method: process() -> Closure",
            10 => "File: {$filePath} -> Namespace: App\Models -> Class: User",
            11 => "File: {$filePath} -> Namespace: App\Models -> Class: User -> Method: filter()",
            12 => "File: {$filePath} -> Namespace: App\Models -> Class: User -> Method: filter() -> Arrow function -> Parameter: item",
            13 => "File: {$filePath} -> Namespace: App\Models",
            14 => "File: {$filePath} -> Namespace: App\Models -> Class: Product -> Property: price",
            15 => "File: {$filePath} -> Namespace: App\Models -> Class: Product",
            16 => "File: {$filePath} -> Namespace: App\Models -> Class: Product -> Method: getPrice()",
            17 => "File: {$filePath} -> Namespace: App\Models -> Class: Product -> Method: getPrice() -> Match expression",
            18 => "File: {$filePath} -> Namespace: App\Helpers",
            19 => "File: {$filePath} -> Namespace: App\Helpers -> Function: helper()",
            20 => "File: {$filePath} -> Namespace: App\Helpers",
            21 => "File: {$filePath} -> Namespace: App\Helpers -> Function: anotherHelper()",
            22 => "File: {$filePath} -> Namespace: App\Helpers -> Function: anotherHelper() -> Closure",
            23 => "File: {$filePath} -> Namespace: App\Traits",
            24 => "File: {$filePath} -> Namespace: App\Traits -> Trait: LoggerTrait",
            25 => "File: {$filePath} -> Namespace: App\Traits -> Trait: LoggerTrait -> Method: log()",
            26 => "File: {$filePath} -> Namespace: App\Enums",
            27 => "File: {$filePath} -> Namespace: App\Enums -> Enum: Status -> Enum case: Active",
            28 => "File: {$filePath} -> Namespace: App\Enums -> Enum: Status",
            29 => "File: {$filePath} -> Namespace: App\Enums -> Enum: Status -> Method: isActive()",
            30 => "File: {$filePath} -> Namespace: App\Factory",
            31 => "File: {$filePath} -> Namespace: App\Factory -> Function: createService()",
            32 => "File: {$filePath} -> Namespace: App\Factory -> Function: createService() -> Class: {anonymous} -> Property: value",
            33 => "File: {$filePath} -> Namespace: App\Factory -> Function: createService() -> Class: {anonymous}",
            34 => "File: {$filePath} -> Namespace: App\Factory -> Function: createService() -> Class: {anonymous} -> Method: getValue()",
            35 => "File: {$filePath} -> Namespace: App\Controller",
            36 => "File: {$filePath} -> Namespace: App\Controller -> Class: UserController",
            37 => "File: {$filePath} -> Namespace: App\Controller -> Class: UserController -> Method: list()",
            38 => "File: {$filePath} -> Namespace: App\Controller -> Class: UserController",
            39 => "File: {$filePath} -> Namespace: App\Controller -> Class: UserController -> Method: create()",
            40 => "File: {$filePath} -> Namespace: App\Controller -> Class: UserController -> Method: item() -> Parameter: callback",
            41 => "File: {$filePath} -> Namespace: App\Controller",
            42 => "File: {$filePath} -> Namespace: App\Controller -> Class: Entity -> Constant: MAX_SIZE",
            43 => "File: {$filePath} -> Namespace: App\Controller -> Class: Entity -> Constant: MIN_SIZE, DEFAULT_SIZE",
            44 => "File: {$filePath} -> Namespace: App\Controller -> Class: Entity -> Property: field",
            45 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Property: propA",
            46 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Property: propA",
            47 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Property: propA",
            48 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Property: propA",
            49 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Property: propD",
            50 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Property: propD",
            51 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Property: propD",
            52 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Property: propD",
            53 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Method: __construct() -> Parameter: propB",
            54 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Method: __construct() -> Parameter: propB",
            55 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Method: __construct() -> Parameter: propB",
            56 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Method: withProp() -> Parameter: propC",
            57 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Method: withProp() -> Parameter: propC",
            58 => "File: {$filePath} -> Namespace: App\Controller -> Class: Multiple -> Method: withProp() -> Parameter: propC",
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
                ContextNodeInterface::KIND_ARROW_FUNCTION => 'Arrow function',
                ContextNodeInterface::KIND_CLASS => 'Class: ' . ($node->getName() ?? '{anonymous}'),
                ContextNodeInterface::KIND_CLASS_CONST => 'Constant: ' . ($node->getName() ?? '{unknown}'),
                ContextNodeInterface::KIND_CLOSURE => 'Closure',
                ContextNodeInterface::KIND_ENUM => "Enum: {$node->getName()}",
                ContextNodeInterface::KIND_ENUM_CASE => "Enum case: {$node->getName()}",
                ContextNodeInterface::KIND_FILE => "File: {$node->getName()}",
                ContextNodeInterface::KIND_FUNCTION => "Function: {$node->getName()}()",
                ContextNodeInterface::KIND_INTERFACE => "Interface: {$node->getName()}",
                ContextNodeInterface::KIND_MATCH => 'Match expression',
                ContextNodeInterface::KIND_METHOD => "Method: {$node->getName()}()",
                ContextNodeInterface::KIND_NAMESPACE => "Namespace: {$node->getName()}",
                ContextNodeInterface::KIND_PARAMETER => 'Parameter: ' . ($node->getName() ?? '{unknown}'),
                ContextNodeInterface::KIND_PROPERTY => 'Property: ' . ($node->getName() ?? '{unknown}'),
                ContextNodeInterface::KIND_TRAIT => "Trait: {$node->getName()}",
                default => ucfirst($node->getKind()) . ($node->getName() ? ': ' . $node->getName() : ''),
            };
        }

        return implode(' -> ', $lines);
    }

    private function getMockSplFileInfo(string $pathname): \SplFileInfo
    {
        $mock = $this->createMock(\SplFileInfo::class);
        $mock->method('getPathname')->willReturn($pathname);

        return $mock;
    }
}
