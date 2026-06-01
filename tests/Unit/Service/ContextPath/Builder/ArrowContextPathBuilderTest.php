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

use Aeliot\TodoRegistrar\Dto\Parsing\PhpContextNode;
use Aeliot\TodoRegistrar\Service\ContextPath\Builder\ArrowContextPathBuilder;
use Aeliot\TodoRegistrarContracts\Context\PhpContextNodeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrowContextPathBuilder::class)]
final class ArrowContextPathBuilderTest extends TestCase
{
    public static function getDataForTestBuild(): iterable
    {
        yield [
            "File: /path/to/file -> Class: MyClass -> Method: doSomething() -> Closure -> Class: {anonymous} -> Property: aProperty\n",
            [
                new PhpContextNode(PhpContextNodeInterface::KIND_FILE, '/path/to/file'),
                new PhpContextNode(PhpContextNodeInterface::KIND_CLASS, 'MyClass'),
                new PhpContextNode(PhpContextNodeInterface::KIND_METHOD, 'doSomething'),
                new PhpContextNode(PhpContextNodeInterface::KIND_CLOSURE, null),
                new PhpContextNode(PhpContextNodeInterface::KIND_CLASS, null),
                new PhpContextNode(PhpContextNodeInterface::KIND_PROPERTY, 'aProperty'),
            ],
        ];
    }

    #[DataProvider('getDataForTestBuild')]
    public function testBuild($expectedPath, array $nodes): void
    {
        self::assertSame($expectedPath, (new ArrowContextPathBuilder())->build($nodes));
    }
}
