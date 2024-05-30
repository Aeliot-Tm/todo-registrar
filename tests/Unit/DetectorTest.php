<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit;

use Aeliot\TodoRegistrar\Detector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Detector::class)]
final class DetectorTest extends TestCase
{
    public function testCollectSingleLine(): void
    {
        $tokens = \PhpToken::tokenize(file_get_contents(__DIR__ . '/../fixtures/single_line.php'));
        $tokens = (new Detector())->filter($tokens);

        self::assertCount(1, $tokens);
        self::assertSame('// TODO single line comment', $tokens[0]->text);
    }
}