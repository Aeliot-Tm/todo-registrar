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
use Aeliot\TodoRegistrar\Service\ContextPath\Builder\NumberedContextPathBuilder;
use Aeliot\TodoRegistrarContracts\ContextNodeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumberedContextPathBuilder::class)]
final class NumberedContextPathBuilderTest extends TestCase
{
    public static function getDataForTestBuild(): iterable
    {
        yield [
            "1. File: /path/to/file\n"
            . "2. Class: MyClass\n"
            . "3. Method: doSomething()\n"
            . "4. Closure\n"
            . "5. Class: {anonymous}\n"
            . "6. Property: aProperty\n",
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
        self::assertSame($expectedPath, (new NumberedContextPathBuilder())->build($nodes));
    }
}
