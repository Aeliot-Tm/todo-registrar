<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit;

use Aeliot\TodoRegistrar\Detector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Detector::class)]
final class DetectorTest extends TestCase
{
    public function testCollectMultilineComment(): void
    {
        $tokens = $this->getTokens( __DIR__ . '/../fixtures/multi_line_comment.php');

        self::assertCount(1, $tokens);

        $expectedContent = <<<CONT
/*
 * Multi line comment
 */
CONT;
        ;
        self::assertSame($expectedContent, $tokens[0]->text);
    }

    public function testCollectMultilineDocBlock(): void
    {
        $tokens = $this->getTokens( __DIR__ . '/../fixtures/multi_line_doc_block.php');

        self::assertCount(1, $tokens);

        $expectedContent = <<<CONT
/**
 * Multi line doc block
 */
CONT;
        ;
        self::assertSame($expectedContent, $tokens[0]->text);
    }

    public function testCollectSingleLine(): void
    {
        $tokens = $this->getTokens(__DIR__ . '/../fixtures/single_line.php');

        self::assertCount(1, $tokens);
        self::assertSame('// TODO single line comment', $tokens[0]->text);
    }

    /**
     * @param string $path
     *
     * @return \PhpToken[]
     */
    private function getTokens(string $path): array
    {
        $tokens = \PhpToken::tokenize(file_get_contents($path));

        return (new Detector())->filter($tokens);
    }
}