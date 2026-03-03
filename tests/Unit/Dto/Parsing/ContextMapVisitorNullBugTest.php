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

namespace Aeliot\TodoRegistrar\Test\Unit\Dto\Parsing;

use Aeliot\TodoRegistrar\Dto\Parsing\ContextMapVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test documenting the NULL bug in ContextMapVisitor found in aus-api project.
 *
 * BUG DESCRIPTION:
 * When processing /home/aeliot/projects/aventus/aus/aus-api/app/src/Service/ImageUploadService.php
 * with version 3.3.0, ContextMapVisitor::collectNodeCommentsPositions() receives null
 * instead of PhpParser\Node, causing TypeError.
 *
 * PROBLEMATIC CODE (ImageUploadService.php:118):
 * public function uploadByUrl(string $url, string $label, User $targetUser = null): bool
 *
 * ROOT CAUSE:
 * - Parameter has non-nullable type (User) with null default value (= null)
 * - This creates null nodes in AST during traversal
 * - collectAllCommentsPositions() calls collectNodeCommentsPositions($node) without null check
 *
 * ERROR MESSAGE:
 * TypeError: Aeliot\TodoRegistrar\Dto\Parsing\ContextMapVisitor::collectNodeCommentsPositions():
 * Argument #1 ($node) must be of type PhpParser\Node, null given
 *
 * FIX:
 * Add null check in collectAllCommentsPositions() before calling collectNodeCommentsPositions()
 *
 * NOTE:
 * This bug is difficult to reproduce in isolated test environment because:
 * 1. It depends on specific file structure and context
 * 2. PHP-parser behavior may vary
 * 3. The bug was confirmed in production but not reproducible with simple fixtures
 *
 * This test serves as documentation and provides a fixture approximating the problem.
 */
#[CoversClass(ContextMapVisitor::class)]
final class ContextMapVisitorNullBugTest extends TestCase
{
    /**
     * Test with fixture approximating the problematic code from aus-api.
     *
     * This test documents the expected behavior but may not reproduce
     * the actual TypeError due to environment differences.
     */
    public function testProcessFileWithNonNullableTypeAndNullDefault(): void
    {
        $fixtureFile = __DIR__ . '/../../../fixtures/php/nullable_parameter_bug.php';
        $this->assertFileExists($fixtureFile, 'Fixture file must exist');

        $code = file_get_contents($fixtureFile);
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($code);

        $this->assertNotNull($ast, 'AST should be parsed successfully');

        self::assertTrue($this->hasNullNode($ast));

        $visitor = new ContextMapVisitor($fixtureFile);
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);

        // In version <3.4.2, this throw TypeError
        $traverser->traverse($ast);
    }

    /**
     * Helper method to check if AST contains null nodes.
     *
     * @param array<\PhpParser\Node|null> $nodes
     */
    private function hasNullNode(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if (null === $node) {
                return true;
            }

            foreach ($node->getSubNodeNames() as $name) {
                $subNodes = $node->$name;
                if (null === $subNodes) {
                    return true;
                }

                if ($subNodes instanceof Node) {
                    $subNodes = [$subNodes];
                }

                if (\is_array($subNodes) && $this->hasNullNode($subNodes)) {
                    return true;
                }
            }
        }

        return false;
    }
}
