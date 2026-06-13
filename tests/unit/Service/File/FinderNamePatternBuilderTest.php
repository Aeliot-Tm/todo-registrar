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

use Aeliot\TodoRegistrar\Service\File\FinderNamePatternBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FinderNamePatternBuilder::class)]
final class FinderNamePatternBuilderTest extends TestCase
{
    public function testBuildFromExtensions(): void
    {
        $builder = new FinderNamePatternBuilder();

        self::assertSame(
            '/\.(?:php|yaml|yml)$/',
            $builder->buildFromExtensions(['php', 'yaml', 'yml']),
        );
    }

    public function testBuildFromExtensionsEscapesSpecialCharacters(): void
    {
        $builder = new FinderNamePatternBuilder();

        self::assertSame(
            '/\.(?:module|inc)$/',
            $builder->buildFromExtensions(['module', 'inc']),
        );
    }
}
