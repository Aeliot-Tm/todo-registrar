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

use Aeliot\TodoRegistrar\Service\File\Finder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Finder::class)]
final class FinderTest extends TestCase
{
    public function testFind(): void
    {
        $finder = (new Finder())
            ->in(__DIR__ . '/../../../fixtures')
            ->sortByName(true);

        self::assertCount(3, $finder);

        $expectedPathnames = [
            __DIR__ . '/../../../fixtures/multi_line_comment.php',
            __DIR__ . '/../../../fixtures/multi_line_doc_block.php',
            __DIR__ . '/../../../fixtures/single_line.php',
        ];
        $actualPathnames = array_map(static fn (\SplFileInfo $file) => $file->getPathname(), iterator_to_array($finder));
        self::assertSame($expectedPathnames, array_values($actualPathnames));
    }
}
