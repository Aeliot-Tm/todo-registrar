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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\ContextPath\Builder;

use Aeliot\TodoRegistrar\Dto\Parsing\ContextNode;
use Aeliot\TodoRegistrar\Service\ContextPath\Builder\CodeBlockContextPathBuilder;
use Aeliot\TodoRegistrarContracts\ContextNodeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodeBlockContextPathBuilder::class)]
final class CodeBlockContextPathBuilderTest extends TestCase
{
    public static function getDataForTestBuild(): iterable
    {
        yield [
            "```\n"
            . "File: /path/to/file\n"
            . "Class: MyClass\n"
            . "Method: doSomething()\n"
            . "Closure\n"
            . "Class: {anonymous}\n"
            . "Property: aProperty\n"
            . '```',
            [
                new ContextNode(ContextNodeInterface::KIND_FILE, '/path/to/file'),
                new ContextNode(ContextNodeInterface::KIND_CLASS, 'MyClass'),
                new ContextNode(ContextNodeInterface::KIND_METHOD, 'doSomething'),
                new ContextNode(ContextNodeInterface::KIND_CLOSURE, null),
                new ContextNode(ContextNodeInterface::KIND_CLASS, null),
                new ContextNode(ContextNodeInterface::KIND_PROPERTY, 'aProperty'),
            ],
        ];
    }

    #[DataProvider('getDataForTestBuild')]
    public function testBuild($expectedPath, array $nodes): void
    {
        self::assertSame($expectedPath, (new CodeBlockContextPathBuilder())->build($nodes));
    }
}
