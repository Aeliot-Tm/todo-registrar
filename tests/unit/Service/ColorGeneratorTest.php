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

use Aeliot\TodoRegistrar\Service\ColorGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ColorGenerator::class)]
final class ColorGeneratorTest extends TestCase
{
    public function testGenerateColorIsDeterministic(): void
    {
        $generator = new ColorGenerator();

        self::assertSame($generator->generateColor('bug'), $generator->generateColor('bug'));
    }

    public function testGenerateColorReturnsHexWithoutLeadingHash(): void
    {
        $color = (new ColorGenerator())->generateColor('feature');

        self::assertMatchesRegularExpression('/^[0-9A-F]{6}$/', $color);
    }
}
